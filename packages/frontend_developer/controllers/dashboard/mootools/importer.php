<?php 
defined('C5_EXECUTE') or die(_("Access Denied."));

Loader::library('archive');
Loader::library('mootools/attribute', FRONTEND_DEVELOPER_PACKAGE_HANDLE);
Loader::library('mootools/importer', FRONTEND_DEVELOPER_PACKAGE_HANDLE);

class PluginArchive extends Archive {

	private $_pluginPackageYAMLFile = "package.yml";
	private $_pluginSourceDirectory = "Source";

	public function __construct() {
		parent::__construct();
		$this->targetDirectory = DIR_APP_UPDATES;
	}

	public function unzip($file) {
		$fh = Loader::helper('file');
		$dirBase = parent::unzip($file);
		$dirFull = $this->getArchiveDirectory($dirBase);
		$dirBase = substr(strrchr($dirFull, '/'), 1);
		return $fh->getTemporaryDirectory().'/'.$file.'/'.$dirBase;
	}
}

class JSONResponse {

	private $message = "";
	private $status	= true;
	private $parameters = array();

	public function setStatus($status) {
		$this->status = $status;
	}
	
	public function setMessage($value) {
		$this->message = $value;
	}

	public function setParameter($name, $value) {
		$this->parameters[$name] = $value;
	}

	public function setParameters($parameters) {
		foreach($parameters as $key => $value) {
			$this->setParameter($key, $value);
		}
	}

	private function send() {
		$result = array(
			"status" => $this->status,
			"message" => $this->message,
			"parameters" => $this->parameters
		);
		$response = array("response" => $result);
		echo @json_encode($response);
		exit;
	}

	public function flush($message = null, $parameters = null, $status = null) {
		if (!empty($status)) $this->setStatus($status);
		if (!empty($message)) $this->setMessage($message);
		if (!empty($parameters)) $this->setParameters($parameters);
		$this->send();
	}

}


class DashboardMootoolsImporterController extends Controller {

	const GITHUB_URL = "http://github.com/";

	private $_importFiles = array();

	public function view() {
		Loader::library("3rdparty/github/phpGitHubApi", FRONTEND_DEVELOPER_PACKAGE_HANDLE);

		$handle = FRONTEND_DEVELOPER_PACKAGE_HANDLE;
		$html  = Loader::helper('html');
		$this->addHeaderItem($html->css('style.css', $handle));
		$this->addHeaderItem($html->javascript("jquery.importWizard.js", $handle));
		$this->addHeaderItem($html->javascript("jquery.progressbar.js", $handle));

		$u = new User();
		$ui = UserInfo::getByID($u->getUserID());
		$username = $ui->getAttribute(MOOTOOLS_GITHUB_USER);

		$rows = array();
		if (!empty($username)) {
			$github = new phpGitHubApi();
			$api = $github->getRepoApi();
			$repositories = $api->getUserRepos($username);
	
			foreach ($repositories as $repos) {
				$key = $repos["name"];
				$rows[$key] = $repos;
			}
		}
		$this->set("pkgHandle", FRONTEND_DEVELOPER_PACKAGE_HANDLE);
		$this->set("uID", $u->getUserID());
		$this->set("username", $username);
		$this->set("repos", $rows);
	}

	public function step1() {
		$repos	= $this->post("repository");

		$u = new User();
		$ui = UserInfo::getByID($u->getUserID());
		$username = $ui->getAttribute(MOOTOOLS_GITHUB_USER);

		$url = "http://github.com/".$username."/".$repos;

		$response = new JSONResponse();
		$response->setStatus(false);

		$result = array();
		if (empty($url)) {
			$response->setMessage(t("URL is not effective. Please confirm it."));
			$response->flush();
		}
		$response->setMessage(t("URL is effective."));
		$response->setParameters(array("user" => $username, "repos" => $repos));
		$response->setStatus(true);
		$response->flush();
	}

	public function step2() {
		Loader::library("3rdparty/github/phpGitHubApi", FRONTEND_DEVELOPER_PACKAGE_HANDLE);

		$user	= $this->post("user");
		$repos	= $this->post("repos");

		$response = new JSONResponse();
		$response->setStatus(false);

		$github = new phpGitHubApi();
		$api = $github->getRepoApi();
		$json = $api->show($user, $repos);

		if ($json === false) {
			$response->setMessage(t("The repository was not able to be confirmed. Please confirm whether the repository exists."));
			$response->flush();
		}
		$response->setMessage(t("The repository was able to be confirmed."));
		$response->setParameters(array("user" => $user, "repos" => $repos));
		$response->setStatus(true);
		$response->flush();
	}

	public function step3() {
		Loader::library("3rdparty/github/phpGitHubApi", FRONTEND_DEVELOPER_PACKAGE_HANDLE);
		
		$user	= $this->post("user");
		$repos	= $this->post("repos");

		$github = new phpGitHubApi();
		$api = $github->getRepoApi();
		$tags = $api->getRepoTags($user, $repos);

		$response = new JSONResponse();
		$response->setStatus(false);

		if (!$tags) {
			$response->setMessage(t("Bad GitHub response. Try again later."));
			$response->flush();
		}

		$tags = array_keys((array) $tags);
//		usort($tags, 'version_compare');
		rsort($tags);

		if (empty($tags)) {
			$response->setMessage(t("GitHub repository has no tags. At least one tag is required."));
			$response->flush();
		}

		$response->setMessage(t("Tag was able to be confirmed."));
		$response->setParameters(array(
			"user" => $user,
			"repos" => $repos,
			"tag" => array_shift($tags)
		));
		$response->setStatus(true);
		$response->flush();
	}

	public function step4() {
		$user	= $this->post("user");
		$repos	= $this->post("repos");
		$tag	= $this->post("tag");

		$response = new JSONResponse();
		$response->setStatus(false);

		$zipURL = sprintf(DashboardMootoolsImporterController::GITHUB_URL."%s/%s/zipball/%s", $user, $repos, $tag);
		$fh = Loader::helper('file');
		$pkg = $fh->getContents($zipURL);
		if (empty($pkg)) {
			$response->setMessage(Package::E_PACKAGE_DOWNLOAD);
			$response->flush();
		}

		$file = time();
		$tmpFile = $fh->getTemporaryDirectory().'/'.$file.'.zip';
		$fp = fopen($tmpFile, "wb");
		if ($fp) {
			fwrite($fp, $pkg);
			fclose($fp);
		} else {
			$response->setMessage(Package::E_PACKAGE_SAVE);
			$response->flush();
		}

		$response->setMessage(t("The archive was able to be downloaded."));
		$response->setParameters(array("user" => $user, "repos" => $repos, "file" => $file));
		$response->setStatus(true);
		$response->flush();
	}

	public function step5() {
		Loader::model('file_set');
		Loader::model('file_list');

		$response = new JSONResponse();
		$response->setStatus(false);

		$user	= $this->post("user");
		$repos	= $this->post("repos");
		$file	= $this->post("file");

		$plugin = new PluginArchive();
		$pluginDir = $plugin->unzip($file);

		$u = new User();
		$fs = FileSet::createAndGetSet($repos, 1, $u->getUserID());

		$importer = new MootoolsPluginImporter($fs);
		$importFiles = $importer->getComponentFiles($pluginDir . "/Source/");

		$resultFiles = array();
		foreach($importFiles as $file) {
			$result = $importer->canImport($file);
			if ($result) {
				$resultFiles[$file] = $importer->addFile($file);
			}
		}

		$response->setMessage(t("Plugin taking was completed."));
		$response->setParameter("files", $resultFiles);
		$response->setStatus(true);
		$response->flush();
	}

	private function _traverse($dir) {
		$dh = opendir($dir);
		if ($dh === false) {
			return false;
		}

		$files = array();
		while (($file = readdir($dh)) !== false) {
			$fileOrDir = $dir . '/' . $file;
			if ($file == '.' || $file == '..') {
				continue;
			}
			switch(filetype($fileOrDir)) {
				case 'file':
					$this->_importFiles[$file] = $fileOrDir;
					break;
				case 'dir':
					$this->_traverse($fileOrDir);
					break;
			}
		}
		closedir($dh);
	}

}
