<?php
/**
 * The class it's used to generate product txt files in the urapidflow format
 * @category Kega
 * @package  Kega_ProductAttributeDefault
 */
?>
<?php
class Kega_ProductAttributeDefault_Helper_Urapidflow extends Mage_Core_Helper_Abstract
{
    protected $urapdiflowDir;

    protected $filename;

    protected $filePath;

    public function setFilename($filename)
    {
        $this->filename = $filename;
    }

    public function getFilename()
    {
        return $this->filename;
    }

	public function setDefaultUrapidflowFilename($profileId, $storeId, $productsExtra = false)
	{
		$fileName = 'profile';
		if ($productsExtra) {
			$fileName = 'profile_extra';
		}
		$this->filename = sprintf($fileName . '-%s_store-%s.txt', $profileId, $storeId);
	}

    public function setDir($dir)
    {
        $this->dir = $dir;
    }

    public function getDir()
    {
        if (empty($this->dir)) {
            if (!is_dir(Mage::getBaseDir('var').DS.'urapidflow')) {
                mkdir(Mage::getBaseDir('var').DS.'urapidflow');
                chmod(Mage::getBaseDir('var').DS.'urapidflow', 0777);

                if (!Mage::getBaseDir('var').DS.'urapidflow'.DS.'product_enricher') {
                    mkdir(Mage::getBaseDir('var').DS.'urapidflow'.DS.'product_enricher');
                    chmod(Mage::getBaseDir('var').DS.'urapidflow'.DS.'product_enricher', 0777);
                }
            }
        }

        $this->dir = Mage::getBaseDir('var').DS.'urapidflow'.DS.'product_enricher';

        return $this->dir;
    }

    public function getLogDir()
    {
        if (!is_dir(Mage::getBaseDir('var').DS.'urapidflow'.DS.'product_enricher'.DS.'log')) {
            mkdir(Mage::getBaseDir('var').DS.'urapidflow'.DS.'product_enricher'.DS.'log');
            chmod(Mage::getBaseDir('var').DS.'urapidflow'.DS.'product_enricher'.DS.'log', 0777);
        }

        return Mage::getBaseDir('var').DS.'urapidflow'.DS.'product_enricher'.DS.'log';
    }

    public function getBackupDir()
    {
        if (!is_dir(Mage::getBaseDir('var').DS.'urapidflow'.DS.'product_enricher'.DS.'backup')) {
            mkdir(Mage::getBaseDir('var').DS.'urapidflow'.DS.'product_enricher'.DS.'backup');
            chmod(Mage::getBaseDir('var').DS.'urapidflow'.DS.'product_enricher'.DS.'backup', 0777);
        }

        return Mage::getBaseDir('var').DS.'urapidflow'.DS.'product_enricher'.DS.'backup';
    }

    /**
     * Generate urapidflow product file
     * We add all product updates in a separate file, one for each profile and store
     *
     */
    public function generateProductFile($header, $data)
    {
        $this->filePath = $this->getDir() . DS . $this->getFilename();

        //Zend_Debug::dump($this->filePath);

        $fp = fopen($this->filePath, 'w');

        // write the header
		if (!empty($header)) {
			fputcsv($fp, $header);
		}

        // write the data
        foreach ($data as $fields) {
            foreach ($fields as $index => $value) {
                if (is_array($value)) {
                    $fields[$index] = implode(';', $value);
                }
            }
            fputcsv($fp, $fields);
        }

        fclose($fp);
    }

}