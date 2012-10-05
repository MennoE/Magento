<?php

class Kega_Vacancy_Model_Vacancycandidate extends Mage_Core_Model_Abstract
{
    protected $_resource;
    protected $_read;
    protected $_name = 'vacancycandidate';

    public function _construct()
    {
        parent::_construct();
        $this->_init('vacancy/vacancycandidate');

        $this->_resource = Mage::getSingleton('core/resource');
        $this->_read = $this->_resource->getConnection('core_read');
    }

    public function getDetails($candidateId)
    {
        $select = $this->_read->select();
        $select->from($this->_name)
               ->where('vacancycandidate_id = ?', $candidateId);
        return $this->_read->fetchRow($select);
    }

    public function getFile($field, $hash)
    {
        $select = $this->_read->select();
        $select->from($this->_name, $field)
               ->where('hash = ?', $hash);
        return $this->_read->fetchOne($select);
    }

    public function insert($data)
    {
        if(!isset($data['hash'])) {
            $data['hash'] = $this->generateUniqueHash();
        }

        if($this->_read->insert($this->_name, $data)) {
            return $this->_read->lastInsertId();
        } else {
            return false;
        }
    }

    /**
     * Copy uploaded file to server and link to candidate.
	 * When FTP option is enable, files will be FTPed to main server
	 * For this ftp.php needs to be available in Vacancy/etc
     *
     * @param int $candidateId
     * @param Array $tempData
     * @return String $tmpFile
     */
    public function addFile($type, $candidateId, $tempData)
    {
        if(!$this->checkFileExtension($tempData['name'])) {
            return false;
        }

		$use_ftp = Mage::app()->getStore()->getConfig('vacancy/vacancy_ftp/vacancy_ftp_usage');

		$tempData['name'] = preg_replace('/[^0-9a-z-_.]+/i', '', $tempData['name']);
        $filename = $type . $candidateId . '_' . $tempData['name'];
        $tmpFile = $use_ftp
			? Mage::getConfig()->getVarDir('vacancies/tmp') . DS . $filename
			: Mage::getConfig()->getVarDir('vacancies') . DS . $filename
		;

        if(move_uploaded_file($tempData['tmp_name'], $tmpFile)) {

			chmod($tmpFile, 0644);
			if ($use_ftp) {

				$ftp_config = 'app/code/local/Kega/Vacancy/etc/ftp.php';
				if (!file_exists($ftp_config)) {
					throw new Exception('Vacancy FTP configuration missing');
				}

				// ftp connect to main server
				$path = 'httpdocs/var/vacancies';
				include 'app/code/local/Kega/Vacancy/etc/ftp.php';

				$ftp = ftp_connect($host);
				if(!ftp_login($ftp, $user, $pass)){
					throw new Exception('Connect to ftp failed');
				}

				ftp_chdir($ftp, $path);                             // switch to vacancies dir.
				ftp_put($ftp, $filename, $tmpFile, FTP_BINARY);     // upload file

				// delete file
				// Do not unlink local file, we need it as attachment! unlink($tmpFile);
			}

            $where = $this->_read->quoteInto('vacancycandidate_id = ?', $candidateId);
            $res =$this->_read->update($this->_name, array(($type . '-upload') => $filename), $where);
            if($res) {
                return $tmpFile;
            }
        }
        return false;
    }

    /**
     * Keep generating hash strings untill there is a unique one.
     *
     * @param int $length
     * @return String
     */
    public function generateUniqueHash($length = 16)
    {
        $hash = $this->generateHash($length);

        $select = $this->_read->select();
        $select->from($this->_name, array('hits' => 'COUNT(*)'))
               ->where('hash = ?', $hash);

        $result = $this->_read->fetchOne($select);


        if($result == 0) {
            return $hash;
        } else {
            return $this->generateUniqueHash($length);
        }
    }

    /**
     * Generate a random hash string
     *
     * @param int $length
     * @return String
     */
    public function generateHash($length = 16)
    {
        $hash = "";
        $possible = "0123456789bcdfghjkmnpqrstvwxyz";

        for($i = 0; $i<$length;$i++) {
            $char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
            $hash .= $char;
        }
        return $hash;
    }

    /**
     * Check if a file is safe to upload.
     *
     * @param String $filename
     * @return Boolean
     */
    public function checkFileExtension($filename)
    {
        $forbidden = array('php', 'php5', 'phtml', 'exe', 'ini', 'js');
        if(in_array($this->_getFileExtension($filename), $forbidden)) {
            return false;
        }
        return true;
    }

    /**
     * Get the extension of a file
     *
     * @param string $filename
     * @return string
     */
    private function _getFileExtension($filename)
    {
        return substr($filename, (strrpos($filename, '.')+1));
    }
}