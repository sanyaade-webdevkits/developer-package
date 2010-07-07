<?php 

defined('C5_EXECUTE') or die(_('Access Denied.'));

class MootoolsPluginBuilderPackage extends Package {

	const PACKAGE_HANDLE = 'mootools_plugin_builder';

	protected $pkgHandle = MootoolsPluginBuilderPackage::PACKAGE_HANDLE;
	protected $appVersionRequired = '5.4.0';
	protected $pkgVersion = '1.0';

	public function getPackageDescription() { 
		return t('Mootools Plugin Builder Package');
	}
	
	public function getPackageName() {
		return t('Mootools Plugin Builder Package');
	}

	public function install() {

		Loader::library('mootools/attribute', MootoolsPluginBuilderPackage::PACKAGE_HANDLE);

		$pkg = parent::install();

		Loader::model('single_page');
		Loader::model('attribute/categories/user');
		Loader::model('attribute/categories/file');

		$singlePages = array(
			"/dashboard/mootools" => array(
				'cName' => 'Mootools Plugin Manager',
				'cDescription'	=> 'Management of mootools plugin'
			),
			"/dashboard/mootools/plugin" => array(
				'cName' => 'plugin',
				'cDescription'	=> 'plugin'
			),
			"/dashboard/mootools/importer" => array(
				'cName' => 'import',
				'cDescription'	=> 'Importing of repository'
			)
		);
		foreach ($singlePages as $key => $page) {
			$collection = SinglePage::add($key, $pkg);
			if (!empty($collection)) {
	        	$collection->update($page);
			}
		}
/*		
		$collection = SinglePage::add("/dashboard/mootools", $pkg);
		if (!empty($collection)) {
        	$collection->update(array('cName' => 'Mootools Plugin Manager', 'cDescription'	=> 'Management of mootools plugin'));
		}

		$collection = SinglePage::add("/dashboard/mootools/plugin", $pkg);
		if (!empty($collection)){
			$collection->update(array('cName' => 'plugin', 'cDescription'	=> 'plugin'));
		}

		$collection = SinglePage::add("/dashboard/mootools/importer", $pkg);
		if (!empty($collection)){
        	$collection->update(array('cName' => 'import', 'cDescription'	=> 'Importing of repository'));
		}
*/
		//The name of the user of github is added to the attribute.
		$values = array(
			"akHandle" => MOOTOOLS_GITHUB_USER, "akName" => "Name of user of github",
			"akIsSearchable" => true, "akIsSearchableIndexed" => true, "akIsAutoCreated" => true, "akIsEditable" => true
		);
		$key = UserAttributeKey::add("text", $values, $pkg);

		
		$fileAttributes = array(
			array(
				"type" => "boolean",
				"values" => array(
					"akHandle" => MOOTOOLS_PLUGIN,
					"akName" => "This file is a plugin of mootools",
					"akIsSearchable" => true,
					"akIsSearchableIndexed" => true,
					"akIsAutoCreated" => true,
					"akIsEditable" => true
				)
			),
			array(
				"type" => "text",
				"values" => array(
					"akHandle" => MOOTOOLS_PLUGIN_LICENSE,
					"akName" => "License of Mootools plugin",
					"akIsSearchable" => true,
					"akIsSearchableIndexed" => true,
					"akIsAutoCreated" => true,
					"akIsEditable" => true
				)
			),
			array(
				"type" => "text",
				"values" => array(
					"akHandle" => MOOTOOLS_PLUGIN_AUTHORS,
					"akName" => "Authors of Mootools plugin",
					"akIsSearchable" => true,
					"akIsSearchableIndexed" => true,
					"akIsAutoCreated" => true,
					"akIsEditable" => true
				)
			),
			array(
				"type" => "select",
				"values" => array(
					"akHandle" => MOOTOOLS_PLUGIN_DEPENDENCES,
					"akName" => "Dependence of mootools plugin",
					"akIsSearchable" => true,
					"akIsSearchableIndexed" => true,
					"akIsAutoCreated" => true,
					"akIsEditable" => true
				)
			)
		);

		foreach ($fileAttributes as $key => $attr) {
			FileAttributeKey::add($attr["type"], $attr["values"], $pkg);	
		}
		
		
/*
		$values = array(
			"akHandle" => MOOTOOLS_PLUGIN, "akName" => "This file is a plugin of mootools",
			"akIsSearchable" => true, "akIsSearchableIndexed" => true, "akIsAutoCreated" => true, "akIsEditable" => true
		);
		$key = FileAttributeKey::add("boolean", $values, $pkg);	

		$values = array(
			"akHandle" => MOOTOOLS_PLUGIN_LICENSE, "akName" => "License of Mootools plugin",
			"akIsSearchable" => true, "akIsSearchableIndexed" => true, "akIsAutoCreated" => true, "akIsEditable" => true
		);
		$key = FileAttributeKey::add("text", $values, $pkg);

		//The Mootools license is added to the attribute. 
		$values = array(
			"akHandle" => MOOTOOLS_PLUGIN_AUTHORS, "akName" => "Authors of Mootools plugin",
			"akIsSearchable" => true, "akIsSearchableIndexed" => true, "akIsAutoCreated" => true, "akIsEditable" => true
		);
		$key = FileAttributeKey::add("text", $values, $pkg);

		//The author of the Mootools plug-in is added to the attribute.
		$values = array(
			"akHandle" => MOOTOOLS_PLUGIN_DEPENDENCES, "akName" => "Dependence of mootools plugin",
			"akIsSearchable" => true, "akIsSearchableIndexed" => true, "akIsAutoCreated" => true, "akIsEditable" => true
		);
		$key = FileAttributeKey::add("select", $values, $pkg);
*/

		$db = Loader::db();
		$db->Replace('atSelectSettings', array(
			'akID' => $key->getAttributeKeyID(),
			'akSelectAllowMultipleValues' => true
		), array('akID'), true);

		BlockType::installBlockTypeFromPackage("builder_form", $pkg);
		BlockType::installBlockTypeFromPackage("github_tags", $pkg);
		BlockType::installBlockTypeFromPackage("github_issues", $pkg);
		BlockType::installBlockTypeFromPackage("github_repository", $pkg);

		PageTheme::add('script_builder', $pkg);
	}

	public function uninstall() {
		parent::uninstall();
	}
}