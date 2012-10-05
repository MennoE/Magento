<?php
/**
 * Backupfile collection model
 * @see Varien_Data_Collection_Filesystem
 *
 * @category   Kega
 * @package    Kega_ProjectManagement
 */
class Kega_ProjectManagement_Model_Backupfile_Collection extends Varien_Data_Collection
{

    /**
     * Filter rendering helper variables
     *
     * @see Varien_Data_Collection::$_filter
     * @see Varien_Data_Collection::$_isFiltersRendered
     */
    private $_filterIncrement = 0;
    private $_filterBrackets = array();
    private $_filterEvalRendered = '';

    /**
     * Gets record data
     *
     * @param string $id
     * @return array|bool array or false if record not found
     */
    public function getRecordById($id)
    {
        if ($this->isLoaded()) {
            $this->_collectFiles();
            $this->_generateAndFilterAndSort();
        }

        $records = $this->_collectedFiles;

        if (isset($records[$id])) return $records[$id];

        return false;
    }


    /**
     * Get files from specified directory recursively (if needed)
     *
     * @param string|array $dir
     */
    protected function _collectFiles()
    {
        $backupfileConfigModel = Mage::getModel('projectmanagement/adminhtml_system_config_backend_serialized_array_backupfile');
        $backupFiles = $backupfileConfigModel->getBackupFiles();

        if (!$backupFiles) {
            $this->_collectedFiles = array();
        } else {
             $this->_collectedFiles = $backupFiles;
        }
    }


    /**
     * Get all ids of collected items
     *
     * @return array
     */
    public function getAllIds()
    {
        return array_keys($this->_items);
    }

    /**
     * Lauch data collecting
     *
     * @param bool $printQuery
     * @param bool $logQuery
     * @return Kega_ProjectManagement_Model_Backupfile_Collection
     */
    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }


        $this->_collectedFiles = array();

        $this->_collectFiles();

        $this->_generateAndFilterAndSort();

        // calculate totals
        $this->_totalRecords = count($this->_collectedFiles);
        $this->_setIsLoaded();

        // paginate and add items
        $from = ($this->getCurPage() - 1) * $this->getPageSize();
        $to = $from + $this->getPageSize() - 1;
        $isPaginated = $this->getPageSize() > 0;

        $cnt = 0;
        foreach ($this->_collectedFiles as $row) {
            $cnt++;
            if ($isPaginated && ($cnt < $from || $cnt > $to)) {
                continue;
            }
            $item = new $this->_itemObjectClass();
            $this->addItem($item->addData($row));
            if (!$item->hasId()) {
                $item->setId($cnt);
            }
        }

        return $this;
    }


    /**
     * With specified collected items:
     *  - generate data
     *  - apply filters
     *  - sort
     *
     * @param string $attributeName '_collectedFiles' | '_collectedDirs'
     */
    private function _generateAndFilterAndSort()
    {
        // generate custom data (as rows with columns) basing on the filenames
        foreach ($this->_collectedFiles as $key => $filename) {
            $this->_collectedFiles[$key] = $this->_generateRow($key, $filename);
        }

        // apply filters on generated data
        if (!empty($this->_filters)) {
            foreach ($this->_collectedFiles as $key => $row) {
                if (!$this->_filterRow($row)) {
                    unset($this->_collectedFiles[$key]);
                }
            }
        }

        /**
         * @todo 26.01.2011 Anda B. - not yet implemented
         */
        if (!empty($this->_orders)) {
            //usort($this->_collectedFiles, array($this, '_usort'));
        }
    }


    /**
     * Generate item row basing on the filename
     *
     * @param string $key
     * @param string $filename
     * @return array
     */
    protected function _generateRow($key, $filename)
    {
        return array(
            'filename' => $filename,
            'basename' => basename($filename),
            'dirname' => dirname($filename),
            'id' => $key,
        );
    }


    /**
     * Supports only like
     *
     * @param string $field
     * @param mixed $cond
     * @param string $type 'and' | 'or'
     * @see Varien_Data_Collection_Db::addFieldToFilter()
     * @return Kega_ProjectManagement_Model_Backupfile_Collection
     */
    public function addFieldToFilter($field, $cond, $type = 'and')
    {

        if (isset($cond['like'])) {
            return $this->addCallbackFilter($field, $cond['like'], $type, array($this, 'filterCallbackLike'));
        }

        return $this;
    }


    public function filterCallbackLike($field, $filterValue, $row)
    {
        $filterValueRegex = str_replace('%', '(.*?)', preg_quote($filterValue, '/'));
        return (bool)preg_match("/^{$filterValueRegex}$/i", $row[$field]);
    }

    /**
     * Set a custom filter with callback
     * The callback must take 3 params:
     *     string $field       - field key,
     *     mixed  $filterValue - value to filter by,
     *     array  $row         - a generated row (before generaring varien objects)
     *
     * @param string $field
     * @param mixed $value
     * @param string $type 'and'|'or'
     * @param callback $callback
     * @param bool $isInverted
     * @return Varien_Data_Collection_Filesystem
     */
    public function addCallbackFilter($field, $value, $type, $callback, $isInverted = false)
    {
        $this->_filters[$this->_filterIncrement] = array(
            'field'       => $field,
            'value'       => $value,
            'is_and'      => 'and' === $type,
            'callback'    => $callback,
            'is_inverted' => $isInverted
        );
        $this->_filterIncrement++;
        return $this;
    }


    /**
     * Invokes specified callback
     * Skips, if there is no filtered key in the row
     *
     * @param callback $callback
     * @param array $callbackParams
     * @return bool
     */
    protected function _invokeFilter($callback, $callbackParams)
    {
        list($field, $value, $row) = $callbackParams;
        if (!array_key_exists($field, $row)) {
            return false;
        }
        return call_user_func_array($callback, $callbackParams);
    }


    /**
     * The filters renderer and caller
     * Aplies to each row, renders once.
     *
     * @param array $row
     * @return bool
     */
    protected function _filterRow($row)
    {
        $result = true;

        // render filters once
        if (!$this->_isFiltersRendered) {
            for ($i = 0; $i < $this->_filterIncrement; $i++) {
                $partialResult = $this->_invokeFilter($this->_filters[$i]['callback'],
                                               array($this->_filters[$i]['field'],
                                                     $this->_filters[$i]['value'], $row)
                                              );
            }
            // return true only if all conditions are true
            $result = $partialResult && $result;
        }
        return $result;
    }




}