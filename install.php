<?php
if (!defined('FREEPBX_IS_AUTH')) { die('No direct script access allowed'); }

/**
 * FreePBX DAHDi Config Module
 *
 * Copyright (c) 2009, Digium, Inc.
 *
 * Author: Ryan Brindley <ryan@digium.com>
 *
 * This program is free software, distributed under the terms of
 * the GNU General Public License Version 2. 
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

global $db;
global $amp_conf;
global $asterisk_conf;

if (! function_exists('out')) {
	function out ($text) {
		echo $text."<br />";
	}
}

if (! function_exists('outn')) {
	function outn ($text) {
		echo $text;
	}
}

if(!$db->getAll('SHOW TABLES LIKE "dahdi_advanced"')) {
	out('Creating Dahdi Advanced Settings Table');
	$sql = "CREATE TABLE IF NOT EXISTS dahdi_advanced (
		`keyword` VARCHAR(50) NOT NULL PRIMARY KEY,
		`val` VARCHAR(255),
		`default_val` VARCHAR(255)
	);";

	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_freepbx($result->getDebugInfo());
	}
	unset($result);

	$entries = array(
		'module_name'=>'wctdm24xxp',
		'tone_region'=>'us',
		'opermode_checkbox'=>0,
		'opermode'=>'USA',
		'alawoverride_checkbox'=>0,
		'alawoverride'=>0,
		'fxs_honor_mode_checkbox'=>0,
		'fxs_honor_mode'=>0,
		'boostringer_checkbox'=>0,
		'boostringer'=>0,
		'lowpower_checkbox'=>0,
		'lowpower'=>0,
		'fastringer_checkbox'=>0,
		'fastringer'=>0,
		'ringdetect_checkbox'=>0,
		'ringdetect'=>0,
		'mwi_checkbox'=>0,
		'mwi'=>'none',
		'neon_voltage'=>'',
		'neon_offlimit'=>'',
		'echocan_nlp_type'=>0,
		'echocan_nlp_threshold'=>'',
		'echocan_nlp_max_supp'=>''
	);

	foreach ($entries as $entry=>$default_val) {
		$sql = "INSERT INTO dahdi_advanced (keyword, default_val) VALUES ('{$entry}', '{$default_val}')";

		$result = $db->query($sql);
		if (DB::IsError($result)) {
			unset($result);
			continue;
		}

		unset($result);
	}
}

if(!$db->getAll('SHOW TABLES LIKE "dahdi_spans"')) {
	out('Creating Dahdi Spans Table');
	$sql = "CREATE TABLE IF NOT EXISTS dahdi_spans (
		`id` INT UNSIGNED NOT NULL PRIMARY KEY auto_increment,
		`span` INT UNSIGNED NOT NULL,
		`active` BOOL DEFAULT 1,
		`alarms` VARCHAR(15),
		`basechan` INT UNSIGNED,
		`coding` VARCHAR(10),
		`coding_opts` VARCHAR(255),
		`context` VARCHAR(255),
		`definedchans` INT UNSIGNED,
		`description` VARCHAR (255),
		`devicetype` VARCHAR(255),
		`framing` VARCHAR(10),
		`framing_opts` VARCHAR(255),
		`group` INT UNSIGNED,
		`irq` VARCHAR(10),
		`lbo` INT UNSIGNED,
		`location` VARCHAR(255),
		`name` VARCHAR(25),
		`manufacturer` VARCHAR (25) DEFAULT 'Digium',
		`max_ch` INT UNSIGNED,
		`min_ch` INT UNSIGNED,
		`pridialplan` VARCHAR(25),
		`prilocaldialplan` VARCHAR(25),
		`reserved_ch` INT UNSIGNED,
		`signalling` VARCHAR(50),
		`spantype` VARCHAR(10),
		`switchtype` VARCHAR(50),
		`syncsrc` INT UNSIGNED,
		`timing` INT UNSIGNED,
		`totchans` INT UNSIGNED,
		`type` VARCHAR(25)
	);";

	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_freepbx($result->getDebugInfo());
	}
	unset($result);
}

if(!$db->getAll('SHOW TABLES LIKE "dahdi_analog"')) {
	out('Creating Dahdi Analog Table');
	$sql = "CREATE TABLE IF NOT EXISTS dahdi_analog (
		`port` INT UNIQUE,
		`type` ENUM ('fxo', 'fxs'),
		`signalling` ENUM ('ks', 'ls'),
		`group` INT UNSIGNED,
		`context` VARCHAR(255)
	);";

	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_freepbx($result->getDebugInfo());
	}
	unset($result);
}

if(!$db->getAll('SHOW TABLES LIKE "dahdi_configured_locations"')) {
	out('Create Configured Locations Table');
	$sql = "CREATE TABLE IF NOT EXISTS dahdi_configured_locations (
		`location` VARCHAR(50),
		`device` VARCHAR(50),
		`basechan` INT,
		`type` VARCHAR(25)
	);";

	$result = $db->query($sql);
	if (DB::IsError($result)) {
		die_freepbx($result->getDebugInfo());
	}
	unset($result);
}

$freepbx_conf =& freepbx_conf::create();

// DAHDISHOWDIGITALCHANS in Advanced Settings of FreePBX
//
$set['value'] = false;
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'dahdiconfig'; //This will help delete the settings when module is uninstalled
$set['category'] = 'DAHDi Configuration Module';
$set['emptyok'] = 0;
$set['name'] = 'Allow PRI Discrete Channels';
$set['description'] = 'DAHDi trunk configuration is normally done using groups for PRI configuration. If there is a need to configure trunks to specific channels, setting this to true will allow each channel to be configured. This can be useful when troubleshooting a PRI and trying to isolate a bad B Channel.';
$set['type'] = CONF_TYPE_BOOL;
$freepbx_conf->define_conf_setting('DAHDISHOWDIGITALCHANS',$set,true);

$set['value'] = true;
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 1;
$set['module'] = 'dahdiconfig'; //This will help delete the settings when module is uninstalled
$set['category'] = 'DAHDi Configuration Module';
$set['emptyok'] = 0;
$set['name'] = 'Disable DAHDi Configuration Writes';
$set['description'] = 'By default the DAHDi configuration module will NOT write out any data to protect any current configuration settings';
$set['type'] = CONF_TYPE_BOOL;
$freepbx_conf->define_conf_setting('DAHDIDISABLEWRITE',$set,true);

$set['value'] = '/etc/init.d/dahdi';
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = 'dahdiconfig'; //This will help delete the settings when module is uninstalled
$set['category'] = 'DAHDi Configuration Module';
$set['emptyok'] = 0;
$set['name'] = 'DAHDi Executable Location';
$set['description'] = 'Location of the DAHDi Executable';
$set['type'] = CONF_TYPE_TEXT;
$freepbx_conf->define_conf_setting('DAHDIEXEC',$set,true);

$set['value'] = '/etc/modprobe.d/dahdi.conf';
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = 'dahdiconfig'; //This will help delete the settings when module is uninstalled
$set['category'] = 'DAHDi Configuration Module';
$set['emptyok'] = 0;
$set['name'] = 'ModProbe.d Configuration File Location';
$set['description'] = 'DAHDi ModProbe.d Configuration File Location (modprobe.d/dahdi.conf)';
$set['type'] = CONF_TYPE_TEXT;
$freepbx_conf->define_conf_setting('DAHDIMODPROBELOC',$set,true);

$set['value'] = '/etc/dahdi/system.conf';
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = 'dahdiconfig'; //This will help delete the settings when module is uninstalled
$set['category'] = 'DAHDi Configuration Module';
$set['emptyok'] = 0;
$set['name'] = 'System Configuration File Location';
$set['description'] = 'DAHDi System Configuration File Location (dahdi/system.conf)';
$set['type'] = CONF_TYPE_TEXT;
$freepbx_conf->define_conf_setting('DAHDISYSTEMLOC',$set,true);

$set['value'] = '/etc/dahdi/modules';
$set['defaultval'] =& $set['value'];
$set['readonly'] = 0;
$set['hidden'] = 0;
$set['level'] = 0;
$set['module'] = 'dahdiconfig'; //This will help delete the settings when module is uninstalled
$set['category'] = 'DAHDi Configuration Module';
$set['emptyok'] = 0;
$set['name'] = 'DAHDi Modules Location';
$set['description'] = 'DAHDi Modules Location (/etc/dahdi/modules)';
$set['type'] = CONF_TYPE_TEXT;
$freepbx_conf->define_conf_setting('DAHDIMODULESLOC',$set,true);


if(!$db->getAll('SHOW TABLES LIKE "dahdi_advanced_modules"')) {
	out("Creating Dahdi Advanced Modules Table");
    $sql = "CREATE TABLE IF NOT EXISTS dahdi_advanced_modules (
        `id` INT UNSIGNED NOT NULL PRIMARY KEY auto_increment,
    	`module_name` VARCHAR(100) UNIQUE,
    	`settings` BLOB
    );";
    $result = $db->query($sql);
    if (DB::IsError($result)) {
    	die_freepbx($result->getDebugInfo());
    }

	out("Migrating Old Data from Dahdi Advanced Table");
    $sql = 'SELECT * FROM dahdi_advanced';
    $oldadv = sql($sql,'getAll',DB_FETCHMODE_ASSOC);

    $settings = array();
    foreach($oldadv as $data) {
        $settings[$data['keyword']] = isset($data['val']) ? $data['val'] : $data['default_val'];
        if (strpos($data['keyword'], 'checkbox')) {
            $settings[$data['keyword']] = $settings[$data['keyword']] == 1 ? TRUE : FALSE;
    	}
    }

    $module_name = $settings['module_name'];
    unset($settings['module_name']);
    unset($settings[$module_name]);

	out("Inserting Old Data from Dahdi Advanced Table");
    $sql = "INSERT IGNORE INTO dahdi_advanced_modules (module_name, settings) VALUES ('".mysql_real_escape_string($module_name)."', '".mysql_real_escape_string(serialize($settings))."')";
    sql($sql);
	
	out("Deleting old dahdi module data from database (its been migrated)");
	foreach ($entries as $entry=>$default_val) {
	    if($entry != 'tone_region') {
	        $sql = "DELETE FROM dahdi_advanced WHERE keyword = '".$entry."'";
	        sql($sql);
	    }
	}
	
	$globalsettings = array(		// global array of values
		'tone_region'=>'us',
	    'language'=>'en', 
	    'busydetect'=>'yes',
	    'busycount'=>'10',
	    'usecallerid'=>'yes',
	    'callwaiting'=>'yes',
	    'usecallingpres'=>'yes',
	    'threewaycalling'=>'yes',
	    'transfer'=>'yes',
	    'cancallforward'=>'yes',
	    'callreturn'=>'yes',
	    'echocancel'=>'yes',
	    'echocancelwhenbridged'=>'no',
	    'echotraining'=>'no',
	    'immediate'=>'no',
	    'faxdetect'=>'no',
	    'rxgain'=>'0.0',
	    'txgain'=>'0.0' 
	    );

	outn('Replacing..');
	foreach($globalsettings as $k => $v) {
		outn('..'.$k.'..');
	    $sql = "REPLACE INTO dahdi_advanced (default_val, keyword) VALUES ('".mysql_real_escape_string($v)."', '".mysql_real_escape_string($k)."')";
	    sql($sql);
	}
	out('..Done');
}

if (!$db->getAll('SHOW COLUMNS FROM dahdi_spans WHERE FIELD = "priexclusive"')) {
	out("Adding priexclusive column");
    $sql = "ALTER TABLE `dahdi_spans` ADD COlUMN `priexclusive` varchar (3) NOT NULL DEFAULT ''";
    $result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM dahdi_spans WHERE FIELD = "reserved_ch"')) {
    if (!$db->getAll('SHOW COLUMNS FROM dahdi_spans WHERE FIELD = "dchannel"')) {
		out("Moving/Adding dchannel column");
        $sql = "ALTER TABLE `dahdi_spans` ADD COlUMN `dchannel` int (5) NOT NULL DEFAULT '0'";
        $result = $db->query($sql);
    }
    
    $sql = "ALTER TABLE `dahdi_spans` change `dchannel` `reserved_ch`  int (5) NOT NULL DEFAULT '0";
    $result = $db->query($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM dahdi_spans WHERE FIELD = "additional_groups"')) {
	out("Adding Additional_groups column");
    $sql = "ALTER TABLE `dahdi_spans` ADD COlUMN `additional_groups` blob";
    $result = $db->query($sql);
}

$sql = "SELECT module_name, settings FROM dahdi_advanced_modules";
$old = sql($sql,'getAll',DB_FETCHMODE_ASSOC);
foreach($old as $list) {
	if(unserialize($list['settings']) !== FALSE) {
		out("Migrating module ".$list['module_name']." from serialized data to json");
	    $o = json_encode(unserialize($list['settings']));
	    $sql = "REPLACE INTO dahdi_advanced_modules (module_name, settings) VALUES ('".mysql_real_escape_string($list['module_name'])."', '".mysql_real_escape_string($o)."')";
	    sql($sql);
	}
}

if(!$db->getAll('SHOW TABLES LIKE "dahdi_modules"')) {
	out('Creating dahdi modules Table');
	$sql = "CREATE TABLE IF NOT EXISTS dahdi_modules (
		`module_name` VARCHAR(100) UNIQUE,
		`settings` BLOB
	);";
	sql($sql);
}

if (!$db->getAll('SHOW COLUMNS FROM dahdi_advanced WHERE FIELD = "type"')) {
	out("Add type column");
	sql('ALTER TABLE dahdi_advanced ADD type varchar(50) default "chandahdi"');
	
	sql('UPDATE dahdi_advanced SET type="system" WHERE keyword="tone_region"');
}

if (!$db->getAll('SHOW COLUMNS FROM dahdi_advanced WHERE FIELD = "additional"')) {
	out("add additional column");
	sql('ALTER TABLE dahdi_advanced ADD additional bool default 1');
	
	foreach($globalsettings as $ksettings => $settings) {
	    sql('UPDATE dahdi_advanced SET additional=0 WHERE keyword="'.$ksettings.'"');
	}
}