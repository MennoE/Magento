<?php
/**
 * @category   Kega
 * @package    Kega_Touch
 */
class Kega_Touch_Model_Observer
{
	/**
	 * Placeholder for the Sqlite (connection) models.
	 * We defined this static so the 'URapidFlow redirect events' can use this connection.
	 * @var Kega_Touch_Model_Sqlite
	 */
	protected static $_sqliteDbs = array();

	/**
	 * Profile / table mappings (Catalog + Stock).
	 * @var array
	 */
	protected $_profileTableMappings = array('catalog' => array('Touch - Product Export' => 'products',
															    'Touch - Category Export' => 'content',
																'Touch - Attribute settings Export' => 'attributeSettings',
															    ),
											 'stock' => array('Touch - Stock Export' => 'stock')
											 );

	/**
	 * Runs URapidFlow 'catalog' profiles and creates catalog.db
	 *
	 * @param   Mage_Cron_Model_Schedule $schedule
	 */
	public function createCatalogDb($schedule)
	{
		$this->createSQLiteDb('catalog');
	}

	/**
	 * Runs URapidFlow 'stock' profiles and creates stock.db
	 *
	 * @param   Mage_Cron_Model_Schedule $schedule
	 */
	public function createStockDb($schedule)
	{
		$this->createSQLiteDb('stock');
	}

	/**
	 * Runs URapidFlow '$dbName' profile's and creates SQLite db.
	 *
	 * @param   Mage_Cron_Model_Schedule $schedule
	 */
	public function createSQLiteDb($dbName)
	{
		@session_start(); // (Bugfix) URapidFlow needs session stuff.
		ini_set("memory_limit","3024M");

		$dir = Mage::getBaseDir() . DS . '_touch' . DS . 'database' . DS;
		if (!Mage::getConfig()->createDirIfNotExists($dir)) {
			Mage::exception('Could not create dir: ' . $dir);
		}

		$dbFileName = $dir . $dbName . '.db';

		// Put the Sqlite (connection) model in a static placeholder.
		// We do this, so the 'URapidFlow redirect events' can use this connection.
		self::$_sqliteDbs[$dbName] = Mage::getModel('kega_touch/sqlite', array($dbFileName . '.tmp', true));

		$this->_runUrapidFlowExport($this->_profileTableMappings[$dbName]);

		if ($dbName == 'catalog') {
			$this->_exportSettings('catalog');
		}

		// Overwrite old db file with the new file.
		@rename($dbFileName . '.tmp', $dbFileName);
	}

	/**
	 * Export all configured Touch export profiles.
	 * They are converted to an SQLite DB with corresponding tables.
	 *
	 * @param array $profiles
	 */
	private function _runUrapidFlowExport($profiles)
	{
		// initialize the locale - this is very important - otherwise all the import rows are flagged as errors
		// because they contain values such as: 'Ja', 'Ingeschakeld' which are valid only in NL locale
		// @see http://www.unirgy.com/wiki/urapidflow/i18n
		Mage::app()->getLocale()->setLocale('nl_NL');
		Mage::app()->getTranslator()->init('global', true);
		Mage::app()->getTranslator()->init('adminhtml', true);

		foreach ($profiles as $runProfile => $tableName) {
			$profile = Mage::getModel('urapidflow/profile')->load($runProfile, 'title');

			echo sprintf('Started URapifFlow profile: %s', $runProfile) . PHP_EOL;

			Mage::helper('urapidflow')->run($runProfile);
			// Because URapidFlow misses the 'urapidflow_catalog_eav_export_before_output'
			// event we fake the usage of it.
			if ($runProfile == 'Touch - Attribute settings Export') {
				$this->_fakeRedirect($profile);
			}

			// Remove empty* output file.
			// * Because we redirect the output to SQLite DB, the output file is empty.
			@unlink($profile->getFileBaseDir() . DS . $profile->getFilename());
		}
	}

	/**
	 * Export app settings to SQLite table.
	 *
	 * @param string $dbName
	 */
	private function _exportSettings($dbName)
	{
		// Initialize needed export table (,true = create table).
		$settings = self::$_sqliteDbs[$dbName]->getTable('settings', true);

		// Convert and import the kega_touch app config.
		$settings->importConfig(Mage::getStoreConfig('kega_touch/app'));
	}

	/**
	 * Observers: urapidflow_catalog_product_export_before_output
	 *
	 * We want to redirect (and adjust) the output before it goes to file.
	 * @see: http://www.unirgy.com/wiki/urapidflow/customization
	 *
	 * @param Varien_Event_Observer $observer
	 */
	public function redirectOutput($observer)
	{
		$vars = $observer->getEvent()->getVars();
		$activeTitle = $vars['profile']->getTitle();

		// Check if the current profile is redirected.
		foreach ($this->_profileTableMappings as $dbName => $profiles) {
			if (isset($profiles[$activeTitle])) {
				$this->_redirectToSQLiteDb($vars, $dbName, $profiles[$activeTitle]);
			}
		}
	}

	/**
	 * Adjust output to needed format and export it to SQLite DB.
	 *
	 * @param array $vars (passed by refference)
	 * @param string $dbName
	 * @param string $tableName
	 */
	private function _redirectToSQLiteDb(array &$vars, $dbName, $tableName)
	{
		if (php_sapi_name() != 'cli') {
			die('Because we redirect the output to SQLite DB it is not possible to run this export profile from the admin.');
		}

		// Initialize needed export table (,true = create table).
		$table = self::$_sqliteDbs[$dbName]->getTable($tableName, true);

		// Convert and import the URapidFlow output.
		$table->importURapidFlowOutput($vars);
	}

	/**
	 * Because URapidFlow misses the 'urapidflow_catalog_eav_export_before_output'
	 * event we fake the usage of it.
	 * In this way we can still use the code we already made (for products), that makes use of events.
	 *
	 * When URapidFlow adds these events we can simply make it work.
	 * (Remove the call to _fakeRedirect, and remove _fakeRedirect method, add event to config.xml)
	 *
	 * @param Unirgy_RapidFlow_Model_Profile $profile
	 */
	private function _fakeRedirect(Unirgy_RapidFlow_Model_Profile $profile)
	{
		$fields = array();
		foreach ($profile->getColumns() as $column) {
				  $fields[$column['alias']] = $column;
		}

		$rows = array();
		$file = $profile->getFileBaseDir() . DS . $profile->getFilename();
		if (($handle = fopen($file, "r")) !== false) {
			 while (($data = fgetcsv($handle, 2048, ",")) !== false) {
							 $rows[] = $data;
			 }
			 fclose($handle);
		}

		// Create fake observer object.
		$event = new Varien_Object(array('vars' => array('profile' => $profile,
														 'rows' => $rows,
														 'fields' => $fields)
														 )
								   );

		$observer = new Varien_Object(array('event' => $event));

		// And use the normal redirect output method (normaly called by event trigger from config.xml).
		$this->redirectOutput($observer);
	}
}