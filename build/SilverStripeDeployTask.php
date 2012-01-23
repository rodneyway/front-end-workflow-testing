<?php


/**
 * A phing task to perform a deployment of a specified
 * tarball to a remote server
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class SilverStripeDeployTask extends SilverStripeBuildTask {
	
	/* deployment config */ 
	private $localpath;
	
	private $package = '';
	
	private $apachegroup = 'apache';
	
	private $remotepath = '';
	
	private $incremental = false;
	
	/* SSH configuration */
	private $host = "";
    private $port = 22;
    private $username = "";
    private $password = "";
    private $pubkeyfile = '';
    private $privkeyfile = '';
    private $privkeyfilepassphrase = '';
	private $ignoreerrors = false;
	
	public function main() {
		
		if (!strlen($this->pubkeyfile) && !strlen($this->password)) {
			// prompt for the password
			$this->password = $this->getInput("Password for ".$this->username.'@'.$this->host);
		} 

		$this->connect();
		
		$releasePath = $this->remotepath.'/releases/'.date('YmdHis');
		$currentPath = $this->remotepath.'/current';
		$remotePackage = $releasePath . '/' . $this->package;
		$localPackage = $this->localpath . '/' . $this->package;
		
		$this->log("Configuring target directories at $releasePath");
		$this->execute("mkdir --mode=2775 -p $releasePath/silverstripe-cache");
		$this->execute("mkdir --mode=2775 -p $releasePath/assets");

		$this->log("Copying deployment package $localPackage");
		$this->copyFile($localPackage, $remotePackage);
		
		if ($this->incremental) {
			$this->log("Copying existing deployment");
			// we use rsync here to be able to use --excludes
			$this->execute("rsync -r --exclude=silverstripe-cache $currentPath/* $releasePath/");
			
			$this->log("Copying configs");
			$this->execute("cp $releasePath/mysite/.assets-htaccess $releasePath/assets/.htaccess");
			$this->execute("cp $currentPath/.htaccess $releasePath/");
			$this->execute("cp $currentPath/_ss_environment.php $releasePath/");
			$this->execute("cp $currentPath/mysite/local.conf.php $releasePath/mysite/local.conf.php");
		}
		
		$this->log("Extracting $remotePackage in $releasePath");
		$this->execute("tar -zx -C $releasePath -f $remotePackage");
		$this->execute("rm $remotePackage");
		
		if (!$this->incremental) {
			$this->log("Copying configs");
			$this->execute("cp $releasePath/mysite/.assets-htaccess $releasePath/assets/.htaccess");
			$this->execute("cp $currentPath/.htaccess $releasePath/");
			$this->execute("cp $currentPath/_ss_environment.php $releasePath/");
			$this->execute("cp $currentPath/mysite/local.conf.php $releasePath/mysite/local.conf.php");

			$this->log("Copying site assets");
			$this->execute("cp -R $currentPath/assets $releasePath");
		}
		
		$this->log("Backing up database");
		$this->execute("php $currentPath/mysite/scripts/backup_database.php");
		
		$this->log("Executing dev/build");
		$this->execute("php $releasePath/sapphire/cli-script.php dev/build");

		$this->log("Changing symlinks");
		$this->execute("rm $currentPath"); 
		$this->execute("ln -s $releasePath $currentPath");

		$this->log("Fixing permissions");
		$this->execute("chgrp -R $this->apachegroup $releasePath");
		$this->execute("find $releasePath -type f -exec chmod 664 {} \;"); 
		$this->execute("find $releasePath -type d -exec chmod 2775 {} \;");

		$this->log("Finalising deployment");
		$this->execute("touch $releasePath/DEPLOYED");
		
		@ssh2_exec($this->connection, 'exit');
	}
	
	/**
	 * Executes a command over SSH
	 *
	 * @param string $cmd
	 *					The command to execute
	 * @param boolean $canFail
	 *					Whether it's okay for the command to fail when it's executed
	 * @return string
	 */
	protected function execute($cmd, $canFail=false) {
		$command = '('.$cmd.'  2>&1) && echo __COMPLETE';
		// $command = 'sh -c '.escapeshellarg('('.$this->command.'  2>&1) && echo __COMPLETE');

        $stream = ssh2_exec($this->connection, $command);
        if (!$stream) {
            throw new BuildException("Could not execute command!");
        }

        stream_set_blocking( $stream, true );
		$data = '';
        while( $buf = fread($stream,4096) ){
            $data .= $buf;
        }

		if (strpos($data, '__COMPLETE') !== false || $this->ignoreerrors || $canFail) {
			$data = str_replace('__COMPLETE', '', $data);
		} else {
			$this->log("Command failed: $command", Project::MSG_WARN);
			throw new BuildException("Failed executing command : $data");
		}
		
        fclose($stream);
		
		return $data;
	}
	
	/**
	 * Copies a file to the remote system
	 *
	 * @param string $local
	 * @param string $remote 
	 */
	protected function copyFile($localEndpoint, $remoteEndpoint)
    {
		ssh2_sftp_mkdir($this->sftp, dirname($remoteEndpoint), 2775, true);
		$ret = ssh2_scp_send($this->connection, $localEndpoint, $remoteEndpoint);

		if ($ret === false) {
			throw new BuildException("Could not create remote file '" . $remoteEndpoint . "'");
		}
    }
	
	/**
	 * Connects SSH stuff up
	 */
	protected function connect() {
		if (!function_exists('ssh2_connect')) { 
            throw new BuildException("To use SshTask, you need to install the SSH extension.");
        }
		
        $this->connection = ssh2_connect($this->host, $this->port);
        if (is_null($this->connection)) {
            throw new BuildException("Could not establish connection to " . $this->host . ":" . $this->port . "!");
        }

        $could_auth = null;
        if (strlen($this->pubkeyfile)) {
            $could_auth = ssh2_auth_pubkey_file($this->connection, $this->username, $this->pubkeyfile, $this->privkeyfile, $this->privkeyfilepassphrase);
        } else {
            $could_auth = ssh2_auth_password($this->connection, $this->username, $this->password);
        }

        if (!$could_auth) {
            throw new BuildException("Could not authenticate connection!");
        }
		
		$this->sftp = ssh2_sftp($this->connection);
	}
	
	
	public function setApachegroup($g) {
		$this->apachegroup = $g;
	}
	public function getApachegroup() {
		return $this->apachegroup;
	}
	
	public function setLocalpath($p) {
		$this->localpath = $p;
	}
	
	public function getLocalpath() {
		return $this->localpath;
	}
	
	public function setRemotepath($p) {
		$this->remotepath = $p;
	}
	
	public function getRemotepath() {
		return $this->remotepath;
	}
	
	public function setPackage($pkg) {
		$this->package = $pkg;
	}
	
	public function getPackage() {
		return $this->package;
	}
	
	public function setIncremental($v) {
		if (!is_bool($v)) {
			$v = $v == 'true' || $v == 1;
		}

		$this->incremental = $v;
	}
	
	public function getIncremental() {
		return $this->incremental;
	}
	
	
	public function setHost($host) 
    {
        $this->host = $host;
    }

    public function getHost() 
    {
        return $this->host;
    }

    public function setPort($port) 
    {
		if (strpos($port, '${') === false) {
			$this->port = $port;
		}
    }

    public function getPort() 
    {
        return $this->port;
    }

    public function setUsername($username) 
    {
        $this->username = $username;
    }

    public function getUsername() 
    {
        return $this->username;
    }

    public function setPassword($password) 
    {
		if (strpos($password, '${') === false) {
			$this->password = $password;
		}
    }

    public function getPassword() 
    {
        return $this->password;
    }
	
    /**
     * Sets the public key file of the user to scp
     */
    public function setPubkeyfile($pubkeyfile)
    {
		if (strpos($pubkeyfile, '${') === false) {
			$this->pubkeyfile = $pubkeyfile;
		}
    }

    /**
     * Returns the pubkeyfile
     */
    public function getPubkeyfile()
    {
        return $this->pubkeyfile;
    }
    
    /**
     * Sets the private key file of the user to scp
     */
    public function setPrivkeyfile($privkeyfile)
    {
        $this->privkeyfile = $privkeyfile;
    }

    /**
     * Returns the private keyfile
     */
    public function getPrivkeyfile()
    {
        return $this->privkeyfile;
    }
    
    /**
     * Sets the private key file passphrase of the user to scp
     */
    public function setPrivkeyfilepassphrase($privkeyfilepassphrase)
    {
        $this->privkeyfilepassphrase = $privkeyfilepassphrase;
    }

    /**
     * Returns the private keyfile passphrase
     */
    public function getPrivkeyfilepassphrase($privkeyfilepassphrase)
    {
        return $this->privkeyfilepassphrase;
    }
    
    public function setCommand($command) 
    {
        $this->command = $command;
    }

    public function getCommand() 
    {
        return $this->command;
    }
	
	public function setIgnoreErrors($ignore) {
		if (!is_bool($ignore)) {
			$ignore = $ignore == 'true' || $ignore == 1;
		}

		$this->ignoreerrors = $ignore;
	}
	
	public function getIgnoreErrors() {
		return $this->ignoreerrors;
	}
}
