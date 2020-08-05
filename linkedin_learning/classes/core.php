<?php
/**
 * LinkdIn Learining API core helper functions and callbacks
 *
 * @package   local_linkdin_learning
 * @copyright 2020 Wal-Mart
 *
 */

require_once(__DIR__.'/../../../config.php');
require_once(__DIR__.'/../../../lib/filelib.php');

class block_linkedin_learning_core {
	
	/** @var string API client id. */
	protected $clientid = "";
	
	/** @var string API client secret. */
    protected $clientsecrect = "";
	
	/** Constant string GRANT_TYPE. */
    protected const GRANT_TYPE = "client_credentials";
    
    /** @var string API client secret. */
	protected $accesstoken = "";
	
	/** Constant string database table name. */
	const LINKEDIN_TABLE = 'linkedin_learning_courses';
	
	/** Plugin Log path */
	protected const LOG_PATH = __DIR__.'/../linkedin_learning_api_log';
    
	/**
     * class constructor
     */
	function __construct() {
		global $CFG;
		
		$this->clientid = $CFG->linkedin_client_id;
		$this->clientsecrect = $CFG->linkedin_client_secret;
		
        $response = $this->accesstoken_generator($this->clientid, $this->clientsecrect);
        if (is_string($response)) {
            $this->accesstoken = $response;
            $this->get_course();
        }
        
	}
    
    /**
     * Get LinkedIn Learning accessToken
     * @params: string $clientid, string $clientsecrect
     * @output: string $accesstoken 
     */
	protected function accesstoken_generator(string $clientid, string $clientsecrect) {
        global $CFG;
    
        //Pick accessToken from cache
        $cache = cache::make('block_linkedin_learning', 'linkedin_api'); //Caches Object
        $expires_in = $cache->get('expires_in');
        $start_time = $cache->get('start_time');
        $cacheflag = 0;
        if (!empty($expires_in) && !empty($start_time)) {
            $timespent = (time() - 60) - (int)$start_time;
            if ($timespent < $expires_in) {
                $accesstoken = $cache->get('accesstoken');
                $cacheflag = 1;
            } else {
                $cache->delete('accesstoken');
                $cache->delete('expires_in');
                $cache->delete('start_time');
            }
        }

        //Request accessToken from LinkedIn API
        if ($cacheflag == 0) {
            $url = 'https://www.linkedin.com/oauth/v2/accessToken'; 
            $curl = new curl();
        
            $curl->setHeader('Content-type: application/x-www-form-urlencoded');
			
            $params = array(
                            'grant_type'=> self::GRANT_TYPE,
                            'client_id' => $clientid, 
                            'client_secret' => $clientsecrect
            );
			
			if (!empty($params)) {
                $url .= (stripos($url, '?') !== false) ? '&' : '?';
                $url .= http_build_query($params, '', '&');
            }

            $response_json = $curl->post($url, $params);
			
            if (is_string($response_json)){
                $response = json_decode($response_json, true);
				
                if (is_array($response) && !isset($response['error']) && !isset($response['serviceErrorCode'])) {
                    $accesstoken = isset($response['access_token'])?$response['access_token']:'';
                    $tokenexpiry = isset($response['expires_in'])?$response['expires_in']:0;
                    
                    $cache->set('accesstoken', $accesstoken);
                    $cache->set('expires_in', $tokenexpiry);
                    $cache->set('start_time', time());
					
                } elseif (isset($response['error']) || isset($response['serviceErrorCode']))  {
					$error_message = isset($response['error']) ? $response['error_description'] : $response['message'];
                    error_log(date("m/d/Y h:i:s")."::".$error_message."\n", 3, self::LOG_PATH);
                    $accesstoken['error'] =  $error_message;
					die();
                } else {
					error_log(date("m/d/Y h:i:s")."::".$response_json."\n", 3, self::LOG_PATH);
                    $accesstoken['error'] =  $response_json;
					die();
				}
            } else {
                error_log(date("m/d/Y h:i:s")."::Not a valid response for access token"."\n", 3, self::LOG_PATH);
                $accesstoken['error'] =  "Not a valid response for access token";
				die();
            }
        }
        
        return $accesstoken;
    }
    
    /**
     * Get LinkedIn Learning Walmart Courses
     * @params: string $clientid, string $clientsecrect
     * @output: string $accesstoken 
     */
	public function get_course() {
		global $CFG;
		
		if(!empty($this->accesstoken) && is_string($this->accesstoken)) {
			$startAt = (mktime(0, 0, 0, date('m'), date('d')-7, date('y')))*1000;
			$urns = $this->get_course_urn($this->accesstoken, $startAt); //Collect 20 popular Course URNs
			if(count($urns) > 0) {
				$this->update_course_metadata($this->accesstoken, $urns);
			} else {
				error_log(date("m/d/Y h:i:s")."::Empty course URN returns"."\n", 3, self::LOG_PATH);
			}
		} else {
			error_log(date("m/d/Y h:i:s")."::Invalid or empty access token"."\n", 3, self::LOG_PATH);
		}
    }
	
	 /**
     * Collect Walmart Popular Courses URNs
     * @params: string $accesstoken
     * @params: $startAt 
     * @output: array $urn 
     */
	public function get_course_urn(string $accesstoken, $startAt) : array {
		global $DB, $CFG;
		$url = 'https://api.linkedin.com/v2/learningActivityReports?q=criteria&contentSource=EXTERNAL&timeOffset.unit=DAY&aggregationCriteria.primary=CONTENT&assetType=COURSE&startedAt='.$startAt.'&=&sortBy.engagementMetricQualifier=TOTAL&timeOffset.duration=1&sortBy.engagementMetricType=SECONDS_VIEWED';
		$urns = array();
		$courseurns = array();
		$curl = new curl();
    
        $curl->setHeader('Authorization: Bearer '.$accesstoken);
        $response_json = $curl->get($url);
		$response = json_decode($response_json, true);
		
        if(isset($response['elements']) && count($response['elements'])) {
			$cntr = 0;
			foreach ($response['elements'] AS $key => $val) {
				if ($val['contentDetails']['locale']['country'] == 'US' && $cntr <=19)  {
					$cntr++;
					$coursedata[] = [
						'urn' => $val['contentDetails']['contentUrn'],
						'title' => $val['contentDetails']['name'],
					];
					array_push($courseurns, $val['contentDetails']['contentUrn']); 
				}
			}
			$DB->execute("TRUNCATE TABLE {".self::LINKEDIN_TABLE."}"); //flush all previous data.
			$DB->insert_records(self::LINKEDIN_TABLE, $coursedata);
			$urns = $courseurns;
		} elseif($response == "") {
			error_log(date("m/d/Y h:i:s").$response_json."\n", 3, self::LOG_PATH);
		}else {
			error_log(date("m/d/Y h:i:s")."::Empty course URN returned"."\n", 3, self::LOG_PATH);
		}
		return $urns;
		
	}
	
	 /**
     * Collect Popular course images
     * @params: string $accesstoken
     * @params: array $urns
     * @output: bool $urn 
     */
	public function update_course_metadata(string $accesstoken, array $urns) {
		global $DB, $CFG;
		
		foreach ($urns as $key => $urn) {
			$url = 'https://api.linkedin.com/v2/learningAssets/'.$urn;
			$curl = new curl();
			$curl->setHeader('Authorization: Bearer '.$accesstoken);
			$response_json = $curl->get($url);
			$response = json_decode($response_json, true);
			
			if(!isset($response['message'])) {
				$updatesql = "UPDATE {linkedin_learning_courses}
								SET image= :image, shortdescription= :shortdescription,
									language= :language, country= :country,
									courselevel= :courselevel, completiontime= :completiontime,
									ssolaunchurl= :ssolaunchurl, weblaunchurl= :weblaunchurl,
									lastupdatedat= :lastupdatedat, publishedate= :publishedate,
									author= :author
								WHERE urn= :urn";
								
				$params = array('image' => $response['details']['images']['primary'],
								'shortdescription' => $response['details']['shortDescription']['value'],
								'language' => $response['details']['shortDescription']['locale']['language'],
								'country' => $response['details']['shortDescription']['locale']['country'],
								'courselevel' => $response['details']['level'], 
								'completiontime' => $response['details']['timeToComplete']['duration'],
								'ssolaunchurl' => $response['details']['urls']['ssoLaunch'],
								'weblaunchurl' => $response['details']['urls']['webLaunch'],
								'lastupdatedat' => $response['details']['lastUpdatedAt'],
								'publishedate' => $response['details']['publishedAt'],
								'author' => $response['details']['contributors'][0]['authorDetails']['firstName']['value']." ".$response['details']['contributors'][0]['authorDetails']['lastName']['value'],
								'urn' => $urn);
				$response = $DB->execute($updatesql, $params);
				file_put_contents(self::LOG_PATH, date("m/d/Y h:i:s")."::Cron job executed and updated course - ".$urn."\n", FILE_APPEND);
			} else {
				error_log(date("m/d/Y h:i:s")."::".$response['message']."\n", 3, self::LOG_PATH);
			}
		}
    }
}
