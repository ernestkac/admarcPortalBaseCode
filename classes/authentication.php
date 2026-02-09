<?php

require_once 'database.php';
require_once 'downloadReport.php';
class Authentication {


	private $PerPost;
	private $fileName;
	private $financelist;
	public $defaultPassword = "Paygo1";
	
	private $databaseObject;

	private $reportFileManagerObject;

	private $databaseNumber;

	public $error = array();

	public function __construct($databaseNumber = 1){
		$this->financelist = array("482217", "764115", "741498");
		$this->databaseNumber = $databaseNumber;
		$this->databaseObject = new Database($databaseNumber);
		$this->reportFileManagerObject = new ReportFileManager();

		if($this->databaseObject){
			//echo "object ready";
		}else{
			var_dump(sqlsrv_errors());
		}
		
	}
	
	public static function encrypt($data, $key='ernestkachingwe') {
		$out = '';
		for ($i = 0; $i < strlen($data); $i++) {
			$out .= chr(ord($data[$i]) ^ ord($key[$i % strlen($key)]));
		}
		return rtrim(strtr(base64_encode($out), '+/', '-_'), '=');
	}


	public static function decrypt($data, $key='ernestkachingwe')  {
		$data = base64_decode(strtr($data, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($data)) % 4));
		$out = '';
		for ($i = 0; $i < strlen($data); $i++) {
			$out .= chr(ord($data[$i]) ^ ord($key[$i % strlen($key)]));
		}
		return $out;
	}



	public	function login($userame, $password) {
		$hash = $this->getPasswordHash($userame);
		if($this->verify_password($password, $hash)){

			if ($this->password_needs_update($hash)) {
				$this->changePassword($userame, $password, $password);
			}

			$query = "select ADMARCPortalUsers.empid, ADMARCPortalUsers.name, email, ".
				"accessLevel,  ISNULL(LstLginDateTime, '1900-01-01 00:00:00.000') as LstLginDateTime, division from ADMARCPortalUsers ".
				
				"where ADMARCPortalUsers.empid = ?;";

				$results = $this->databaseObject->PerformQuery( $query, [$userame]);
			
				if ($results)
					return $results;
		}
		return false;
		
	}

	public	function getPasswordHash($userame) {
		$query = "select ADMARCPortalUsers.WCCode from ADMARCPortalUsers ".
			
			"where ADMARCPortalUsers.empid = ?;";

			$results = $this->databaseObject->PerformQuery( $query, [$userame]);
		
			if ($results){
				while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC))
					return $row['WCCode'];
			}
				
		return false;
		
	}
	public	function hashPasswords() {
		$query = "select ADMARCPortalUsers.empid, ADMARCPortalUsers.WCCode from ADMARCPortalUsers WHERE wccode NOT LIKE '\$argon2%';";

			$results = $this->databaseObject->PerformQuery( $query);
		
			if ($results){
				
    		while ($row = sqlsrv_fetch_array($results, SQLSRV_FETCH_ASSOC)) {
				$password = $row['WCCode'];
				$empid = $row['empid'];
				$hashedPassword = $this->create_password_hash($password);
			

				$query = "update ADMARCPortalUsers set  WCCode = ? ".
						"where empid = ?;";
					//var_dump($query);
					$hashresults = $this->databaseObject->PerformQuery( $query, [$hashedPassword,$empid]);
					//var_dump($results);
					//var_dump(sqlsrv_errors());
						if ($hashresults)
							echo "$this->databaseNumber User ID $empid password hashed successfully.\n";
			}
		}


		
	}

	
	public	function loginOther($userame) {
		//echo "log in others called";
		$query = "select ADMARCPortalUsers.empid, ADMARCPortalUsers.name, email, ".
			"accessLevel, ADMARCPortalUsers.WCCode,  ISNULL(LstLginDateTime, '1900-01-01 00:00:00.000') as LstLginDateTime, division from ADMARCPortalUsers ".
			
			"where ADMARCPortalUsers.empid = ?;";

			$results = $this->databaseObject->PerformQuery( $query, [$userame]);
		
			if ($results)
				return $results;
		return false;
	}

	
	public	function changePassword($userame, $newpassword, $password = false) {

		if($this->verify_password($password, $this->getPasswordHash($userame)) || !$password){
			$hash = $this->create_password_hash($newpassword);
			$query = "update ADMARCPortalUsers set LstLginDateTime = getdate(), WCCode = ? ".
				"where empid = ?;";
			//var_dump($query);
			$results = $this->databaseObject->PerformQuery( $query, [$hash,$userame]);
			//var_dump($results);
			//var_dump(sqlsrv_errors());
				if ($results)
					return $this->login($userame, $newpassword);
		
		}
		
		return false;
		
	}

	public	function setLastLoginDatetime($userame, $password) {
		$query = "update ADMARCPortalUsers set LstLginDateTime = getdate() ".
			"where empid = '".$userame."' and WCCode = '".$password."';";
		//var_dump($query);
		$results = $this->databaseObject->PerformQuery( $query);
		//var_dump($results);
		//var_dump(sqlsrv_errors());
			if ($results)
				return $this->login($userame, $password);
		
		return false;
		
	}


	/**
	 *
	 * @param string $password Plaintext password
	 * @param array  $options  Optional options like ['cost' => 12] for BCRYPT,
	 *                         or ['memory_cost'=>1<<17,'time_cost'=>4,'threads'=>2] for Argon2.
	 * @return string Password hash (store this in DB)
	 */
	function create_password_hash(string $password, array $options = []): string {
		// If Argon2id is available, prefer it, else fallback to bcrypt.
		if (defined('PASSWORD_ARGON2ID')) {
			$algo = PASSWORD_ARGON2ID;
			// sensible defaults if none provided - tune for your server
			$defaultOptions = [
				'memory_cost' => 1<<17, // 131072 KB (~128 MB)
				'time_cost'   => 4,
				'threads'     => 2,
			];
			$options = array_merge($defaultOptions, $options);
		} else {
			$algo = PASSWORD_BCRYPT;
			$defaultOptions = ['cost' => 12]; // adjust cost according to server capability
			$options = array_merge($defaultOptions, $options);
		}

		// Optional: add an application-wide pepper (kept outside DB, e.g., in env file)
		// $pepper = getenv('PASSWORD_PEPPER') ?: '';
		// $passwordToHash = $password . $pepper;
		// return password_hash($passwordToHash, $algo, $options);

		return password_hash($password, $algo, $options);
	}

	/*
	 *
	 * @param string $password Plaintext password provided by user
	 * @param string $hash     Stored hash from DB
	 * @return bool True if password matches, false otherwise
	 */
	function verify_password(string $password, string $hash): bool {
		// If you used a pepper when hashing, append same pepper here:
		// $pepper = getenv('PASSWORD_PEPPER') ?: '';
		// return password_verify($password . $pepper, $hash);

		return password_verify($password, $hash);
	}

	/**
	 * Check whether a stored hash needs rehashing (e.g., algorithm or cost changed).
	 * If it returns true you should re-hash with create_password_hash() after successful login.
	 *
	 * @param string $hash    Stored hash
	 * @param array  $options Same options you'd use for create_password_hash
	 * @return bool True if rehash recommended
	 */
	function password_needs_update(string $hash, array $options = []): bool {
		if (defined('PASSWORD_ARGON2ID')) {
			$algo = PASSWORD_ARGON2ID;
			$defaultOptions = [
				'memory_cost' => 1<<17,
				'time_cost'   => 4,
				'threads'     => 2,
			];
		} else {
			$algo = PASSWORD_BCRYPT;
			$defaultOptions = ['cost' => 12];
		}
		$options = array_merge($defaultOptions, $options);
		return password_needs_rehash($hash, $algo, $options);
	}



}
?>
