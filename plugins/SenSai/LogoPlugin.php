<?php

namespace SenSai;

use FML\Controls\Frame;
use FML\Controls\Quad;
use FML\ManiaLink;
use ManiaControl\ManiaControl;
use ManiaControl\Manialinks\ManialinkManager;
use ManiaControl\Manialinks\IconManager;
use ManiaControl\Plugins\Plugin;
use ManiaControl\Callbacks\CallbackListener;
use ManiaControl\Callbacks\Callbacks;
use ManiaControl\Players\Player;
use ManiaControl\Players\PlayerManager;
use ManiaControl\Settings\Setting;
use ManiaControl\Settings\SettingManager;
use ManiaControl\Callbacks\TimerListener;
use ManiaControl\Callbacks\TimerManager;


/**
 * ManiaControl Logo Plugin
 *
 * @author G]R [SG-1] Sen-Sai
 */
class LogoPlugin implements Plugin, CallbackListener, TimerListener {
	/**
	 * Constants
	 */
	const PLUGIN_ID      = 37;
	const PLUGIN_VERSION = 1.13;
	const PLUGIN_NAME    = 'LogoPlugin';
	const PLUGIN_AUTHOR  = 'G]R [SG-1]Sen-sai';
	const DATE           = 'd-m-y h:i:sa T';
	const MLID_LOGO      = 'LogoPlugin.MLID';

	const SETTING_00_LOGO_MODE       = '01. Rotate all logos or show only first';
	const SETTING_01_LOGO_INTERVAL   = '02. Rotation in seconds';
	const SETTING_02_LOGO_HIDE       = '03. Hide at map end?';
	const SETTING_03_LOGO_WHICH      = '04. If only one logo, which one?';
	const SETTING_04_LOGO_IMG_URL    = '05. Logo 1 URL';
	const SETTING_06_LOGO_2_IMG_URL  = '07. Logo 2 URL';
	const SETTING_08_LOGO_3_IMG_URL  = '09. Logo 3 URL';
	const SETTING_10_LOGO_4_IMG_URL  = '11. Logo 4 URL';
	const SETTING_12_LOGO_5_IMG_URL  = '13. Logo 5 URL';
	const SETTING_05_LOGO_URL        = '06. Goto 1 URL';
	const SETTING_07_LOGO_2_URL      = '08. Goto 2 URL';
	const SETTING_09_LOGO_3_URL      = '10. Goto 3 URL';
	const SETTING_11_LOGO_4_URL      = '12. Goto 4 URL';
	const SETTING_13_LOGO_5_URL      = '14. Goto 5 URL';
	const SETTING_14_LOGO_ROYAL_POSX = '15. Logo-Widget-Position-Royal: X';
	const SETTING_15_LOGO_ROYAL_POSY = '16. Logo-Widget-Position-Royal: Y';
	const SETTING_16_LOGO_ELITE_POSX = '17. Logo-Widget-Position-Elite: X';
	const SETTING_17_LOGO_ELITE_POSY = '18. Logo-Widget-Position-Elite: Y';
	const SETTING_18_LOGO_SIZEX      = '19. Logo-Widget-Size: X';
	const SETTING_19_LOGO_SIZEY      = '20. Logo-Widget-Size: Y';


	/**
	 * Private properties
	 */
	/** @var maniaControl $maniaControl */
	private $maniaControl = null;
	private $logodetails  = array();
	private $logo         = array();
	private $logoIndex    = 0;
	private $rotate       = false;
	private $oneLogo      = array();
	private $hide         = false;

	/**
	 * Prepares the Plugin
	 *
	 * @param ManiaControl $maniaControl
	 * @return mixed
	 */
	public static function prepare(ManiaControl $maniaControl) {

	}

	/**
	 * @see \ManiaControl\Plugins\Plugin::load()
	 */
	public function load(ManiaControl $maniaControl) {
		$this->maniaControl = $maniaControl;

		// Init settings
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_00_LOGO_MODE, array('only show logo 1', 'rotate all logos'));
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_01_LOGO_INTERVAL, 10);
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_02_LOGO_HIDE, array('no', 'yes'));
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_03_LOGO_WHICH, array('1', '2', '3', '4', '5'));
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_04_LOGO_IMG_URL, 'http://files.designburo.nl/shootmania/images/glrimratten.png');
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_05_LOGO_URL, 'http://designburo.nl/shootmania');
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_06_LOGO_2_IMG_URL, 'http://files.designburo.nl/shootmania/images/glrimratten.png');
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_07_LOGO_2_URL, 'http://designburo.nl/shootmania');
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_08_LOGO_3_IMG_URL, 'http://files.designburo.nl/shootmania/images/glrimratten.png');
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_09_LOGO_3_URL, 'http://designburo.nl/shootmania');
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_10_LOGO_4_IMG_URL, '');
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_11_LOGO_4_URL, '');
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_12_LOGO_5_IMG_URL, '');
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_13_LOGO_5_URL, '');

		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_14_LOGO_ROYAL_POSX, -148);
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_15_LOGO_ROYAL_POSY, 64);
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_16_LOGO_ELITE_POSX, -148);
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_17_LOGO_ELITE_POSY, -3);
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_18_LOGO_SIZEX, 20);
		$this->maniaControl->getSettingManager()->initSetting($this, self::SETTING_19_LOGO_SIZEY, 20);
		$this->maniaControl->getCallbackManager()->registerCallbackListener(PlayerManager::CB_PLAYERCONNECT, $this, 'handlePlayerConnect');
		$this->maniaControl->getCallbackManager()->registerCallbackListener(SettingManager::CB_SETTING_CHANGED, $this, 'handleSettingChangedCallback');


		$this->loadLogo();

		return true;
	}

	/**
	 * @see \ManiaControl\Plugins\Plugin::unload()
	 */
	public function unload() {
		$this->logo = array();
		$this->maniaControl->getManialinkManager()->hideManialink(self::MLID_LOGO);
	}

	public function loadIntoArray() {
		$this->logo = array();
		$t          = 0;
		// logo 1, default
		if ($this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_04_LOGO_IMG_URL) != '') {
			$this->logo[$t]['url'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_04_LOGO_IMG_URL);
			if ($this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_05_LOGO_URL) != '') {
				$this->logo[$t]['lnk'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_05_LOGO_URL);
			} else {
				$this->logo[$t]['lnk'] = "";
			}
			$t++;
		}
		// logo 2
		if ($this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_06_LOGO_2_IMG_URL) != '') {
			$this->logo[$t]['url'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_06_LOGO_2_IMG_URL);
			if ($this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_07_LOGO_2_URL) != '') {
				$this->logo[$t]['lnk'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_07_LOGO_2_URL);
			} else {
				$this->logo[$t]['lnk'] = "";
			}
			$t++;
		}
		// logo 3
		if ($this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_08_LOGO_3_IMG_URL) != '') {
			$this->logo[$t]['url'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_08_LOGO_3_IMG_URL);
			if ($this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_09_LOGO_3_URL) != '') {
				$this->logo[$t]['lnk'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_09_LOGO_3_URL);
			} else {
				$this->logo[$t]['lnk'] = "";
			}
			$t++;
		}
		// logo 4
		if ($this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_10_LOGO_4_IMG_URL) != '') {
			$this->logo[$t]['url'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_10_LOGO_4_IMG_URL);
			if ($this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_11_LOGO_4_URL) != '') {
				$this->logo[$t]['lnk'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_11_LOGO_4_URL);
			} else {
				$this->logo[$t]['lnk'] = "";
			}
			$t++;
		}
		// logo 5
		if ($this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_12_LOGO_5_IMG_URL) != '') {
			$this->logo[$t]['url'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_12_LOGO_5_IMG_URL);
			if ($this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_13_LOGO_5_URL) != '') {
				$this->logo[$t]['lnk'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_13_LOGO_5_URL);
			} else {
				$this->logo[$t]['lnk'] = "";
			}
			$t++;
		}

	}

	public function loadLogo() {

		$this->loadIntoArray();
		if (count($this->logo) == 0) {
			$error = 'No image has been found (check Settings)!';
			throw new \Exception($error);
		}

		if (strtolower($this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_02_LOGO_HIDE)) != 'no') {
			// Register callbacks begin and end map
			$this->hide = true;
			$this->maniaControl->getCallbackManager()->registerCallbackListener(Callbacks::BEGINMAP, $this, 'handleBeginMap');
			$this->maniaControl->getCallbackManager()->registerCallbackListener(Callbacks::ENDMAP, $this, 'handleEndMap');
		} else {
			$this->hide = false;
		}
		if (strtolower($this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_00_LOGO_MODE)) != 'only show logo 1') {
			$this->rotate = true;
		} else {
			$this->rotate = false;
			$wich         = (int) $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_03_LOGO_WHICH);
			if ($wich == 1) {
				$this->oneLogo['url'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_04_LOGO_IMG_URL);
				$this->oneLogo['lnk'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_05_LOGO_URL);
			}
			if ($wich == 2) {
				$this->oneLogo['url'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_06_LOGO_2_IMG_URL);
				$this->oneLogo['lnk'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_07_LOGO_2_URL);
			}
			if ($wich == 3) {
				$this->oneLogo['url'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_08_LOGO_3_IMG_URL);
				$this->oneLogo['lnk'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_09_LOGO_3_URL);
			}
			if ($wich == 4) {
				$this->oneLogo['url'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_10_LOGO_4_IMG_URL);
				$this->oneLogo['lnk'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_11_LOGO_4_URL);
			}
			if ($wich == 5) {
				$this->oneLogo['url'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_12_LOGO_5_IMG_URL);
				$this->oneLogo['lnk'] = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_13_LOGO_5_URL);
			}
		}
		if ($this->rotate) {
			$timer = (int) $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_01_LOGO_INTERVAL) * 1000;
			$this->maniaControl->getTimerManager()->registerTimerListening($this, 'handleTimer', (int) $timer);
		} else {
			$this->displayLogoWidget($this->oneLogo['url'], $this->oneLogo['lnk']);
		}

	}

	public function handleBeginMap() {

		if ($this->rotate) {

			$timer = (int) $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_01_LOGO_INTERVAL) * 1000;
			$this->maniaControl->getTimerManager()->registerTimerListening($this, 'handleTimer', (int) $timer);
		} else {
			$this->displayLogoWidget($this->oneLogo['url'], $this->oneLogo['lnk']);
		}
	}

	public function handleEndMap() {
		$this->maniaControl->getManialinkManager()->hideManialink(self::MLID_LOGO);
		if ($this->rotate) {
			$this->maniaControl->getTimerManager()->unregisterTimerListening($this, 'handleTimer');
		}
	}


	public function displayLogoWidget($logo, $url, $login = false) {
		if ($this->maniaControl->getServer()->titleId == 'SMStormRoyal@nadeolabs') {
			$x = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_14_LOGO_ROYAL_POSX);
			$y = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_15_LOGO_ROYAL_POSY);
		} else {
			$x = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_16_LOGO_ELITE_POSX);
			$y = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_17_LOGO_ELITE_POSY);
		}
		$sizex     = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_18_LOGO_SIZEX);
		$sizey     = $this->maniaControl->getSettingManager()->getSettingValue($this, self::SETTING_19_LOGO_SIZEY);
		$maniaLink = new ManiaLink(self::MLID_LOGO);
		$frame     = new Frame();
		$maniaLink->add($frame);
		$frame->setSize($sizex, $sizey);
		$frame->setPosition($x, $y);
		$backgroundQuad = new Quad();
		$frame->add($backgroundQuad);
		$backgroundQuad->setSize($sizex, $sizey);
		$backgroundQuad->setImage($logo);
		if (isset($url)) {
			$backgroundQuad->setUrl($url);
		}
		$this->maniaControl->getManialinkManager()->sendManialink($maniaLink, $login);
	}

	public function handlePlayerConnect(Player $player) {
		// Display Widget
		$this->displayLogoWidget($this->logo[$this->logoIndex]['url'], $this->logo[$this->logoIndex]['lnk'], $player->login);
	}

	public function handleTimer($login = false) {
		//$this->maniaControl->getChat()->sendInformation("Rotation mode:");
		//$this->maniaControl->getChat()->sendInformation("[".$this->logoIndex."/".count($this->logo)."]");
		//$this->maniaControl->getChat()->sendInformation($this->logo[$this->logoIndex]['lnk']);
		//$this->maniaControl->getChat()->sendInformation($this->logo[$this->logoIndex]['url']);
		$this->displayLogoWidget($this->logo[$this->logoIndex]['url'], $this->logo[$this->logoIndex]['lnk']);
		$this->logoIndex++;
		if ($this->logoIndex == count($this->logo)) {
			$this->logoIndex = 0;
		}
	}


	// handle scriptsettings changes
	public function handleSettingChangedCallback(Setting $setting) {

		if (!$setting->belongsToClass($this)) {
			return;
		}

		if ($this->rotate) {
			$this->maniaControl->getTimerManager()->unregisterTimerListening($this, 'handleTimer');
		}
		//		$this->unregisterTimerListening(TimerListener $listener, $method)
		$this->maniaControl->getChat()->sendInformation("Settings changed");
		$this->loadLogo();
	}

	/**
	 * @see \ManiaControl\Plugins\Plugin::getId()
	 */
	public static function getId() {
		return self::PLUGIN_ID;
	}

	/**
	 * @see \ManiaControl\Plugins\Plugin::getName()
	 */
	public static function getName() {
		return self::PLUGIN_NAME;
	}

	/**
	 * @see \ManiaControl\Plugins\Plugin::getVersion()
	 */
	public static function getVersion() {
		return self::PLUGIN_VERSION;
	}

	/**
	 * @see \ManiaControl\Plugins\Plugin::getAuthor()
	 */
	public static function getAuthor() {
		return self::PLUGIN_AUTHOR;
	}

	/**
	 * @see \ManiaControl\Plugins\Plugin::getDescription()
	 */
	public static function getDescription() {
		return 'Show your server logo during game,';
	}


}
