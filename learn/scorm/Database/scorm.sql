-- phpMyAdmin SQL Dump
-- version 2.6.2-pl1
-- http://www.phpmyadmin.net
-- 
-- 主機: localhost
-- 建立日期: Feb 14, 2006, 09:09 AM
-- 伺服器版本: 4.0.21
-- PHP 版本: 4.3.11
-- 
-- 資料庫: `EDI_10001`
-- 

-- --------------------------------------------------------

-- 
-- 資料表格式： `cmi_comments_from_learner`
-- 

DROP TABLE IF EXISTS `cmi_comments_from_learner`;
CREATE TABLE IF NOT EXISTS `cmi_comments_from_learner` (
  `Course_ID` int(10) unsigned default NULL,
  `SCO_ID` varchar(50) default NULL,
  `User_ID` varchar(50) default NULL,
  `n` int(11) default NULL,
  `comment` text,
  `location` text,
  `timestamp` varchar(50) default NULL
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- 資料表格式： `cmi_comments_from_lms`
-- 

DROP TABLE IF EXISTS `cmi_comments_from_lms`;
CREATE TABLE IF NOT EXISTS `cmi_comments_from_lms` (
  `Course_ID` int(10) unsigned default NULL,
  `SCO_ID` varchar(50) default NULL,
  `User_ID` varchar(50) default NULL,
  `n` int(11) default NULL,
  `comment` text,
  `location` text,
  `timestamp` varchar(50) default NULL
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- 資料表格式： `cmi_core`
-- 

DROP TABLE IF EXISTS `cmi_core`;
CREATE TABLE IF NOT EXISTS `cmi_core` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `Scorm_Type` varchar(50) default NULL,
  `lesson_location` text,
  `credit` varchar(50) default NULL,
  `lesson_status` varchar(50) default NULL,
  `duration` varchar(50) default NULL,
  `lesson_mode` varchar(50) default NULL,
  `exit_value` varchar(50) default NULL,
  `session_time` text,
  `suspend_data` text,
  `launch_data` text,
  `entry` varchar(50) default NULL,
  `score_raw` varchar(50) default NULL,
  `score_min` varchar(50) default NULL,
  `score_max` varchar(50) default NULL,
  `score_normalized` varchar(50) default NULL,
  `last_time` varchar(50) default NULL,
  `isSuspended` varchar(50) default NULL,
  `success_status` varchar(50) default NULL,
  `completion_status` varchar(50) default NULL,
  `attempt_count` varchar(50) default NULL,
  `isDisabled` varchar(50) default NULL,
  `isHiddenFromChoice` varchar(50) default NULL,
  `attempt_absolut_duration` varchar(50) default NULL,
  `attempt_experienced_duration` varchar(50) default NULL,
  `activity_absolut_duration` varchar(50) default NULL,
  `activity_experienced_duration` varchar(50) default NULL,
  `completion_threshold` varchar(50) default NULL,
  `progress_measure` varchar(50) default NULL
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- 資料表格式： `cmi_interactions`
-- 

DROP TABLE IF EXISTS `cmi_interactions`;
CREATE TABLE IF NOT EXISTS `cmi_interactions` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `n` int(11) default NULL,
  `id` varchar(50) default NULL,
  `timestamp` varchar(50) default NULL,
  `type` varchar(50) default NULL,
  `weighting` varchar(50) default NULL,
  `learner_response` text,
  `result` varchar(50) default NULL,
  `latency` varchar(50) default NULL,
  `description` text,
  `objectives_count` int(11) default NULL,
  `correct_rese_count` int(11) default NULL
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- 資料表格式： `cmi_interactions_correct_response`
-- 

DROP TABLE IF EXISTS `cmi_interactions_correct_response`;
CREATE TABLE IF NOT EXISTS `cmi_interactions_correct_response` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `n` int(11) NOT NULL default '0',
  `id` varchar(50) NOT NULL default '',
  `m` int(11) NOT NULL default '0',
  `pattern` text
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- 資料表格式： `cmi_interactions_objectives`
-- 

DROP TABLE IF EXISTS `cmi_interactions_objectives`;
CREATE TABLE IF NOT EXISTS `cmi_interactions_objectives` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `n` int(11) NOT NULL default '0',
  `id` varchar(50) NOT NULL default '',
  `c` int(11) NOT NULL default '0',
  `objectives_id` varchar(50) NOT NULL default ''
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- 資料表格式： `cmi_objectives`
-- 

DROP TABLE IF EXISTS `cmi_objectives`;
CREATE TABLE IF NOT EXISTS `cmi_objectives` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `n` int(11) NOT NULL default '0',
  `id` varchar(50) default NULL,
  `score_raw` int(11) default NULL,
  `score_min` int(11) default NULL,
  `score_max` int(11) default NULL,
  `status` varchar(50) default NULL
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- 資料表格式： `cmi_student_preference`
-- 

DROP TABLE IF EXISTS `cmi_student_preference`;
CREATE TABLE IF NOT EXISTS `cmi_student_preference` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `audio` varchar(50) default NULL,
  `language` text,
  `speed` varchar(50) default NULL,
  `text` varchar(50) default NULL
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- 資料表格式： `global_objectives`
-- 

DROP TABLE IF EXISTS `global_objectives`;
CREATE TABLE IF NOT EXISTS `global_objectives` (
  `Course_ID` int(10) unsigned NOT NULL default '0',
  `SCO_ID` varchar(50) NOT NULL default '',
  `User_ID` varchar(50) NOT NULL default '',
  `id` varchar(50) NOT NULL default '',
  `global_to_system` varchar(50) NOT NULL default '',
  `ProgressStatus` varchar(50) default NULL,
  `SatisfiedStatus` varchar(50) default NULL,
  `MeasureStatus` varchar(50) default NULL,
  `NormalizedMeasure` varchar(50) default NULL
) TYPE=MyISAM;

-- --------------------------------------------------------

-- 
-- 資料表格式： `sequencing_random_result`
-- 

DROP TABLE IF EXISTS `sequencing_random_result`;
CREATE TABLE IF NOT EXISTS `sequencing_random_result` (
  `Course_ID` int(10) unsigned default NULL,
  `SCO_ID` varchar(50) default NULL,
  `User_ID` varchar(50) default NULL,
  `Random_Child_ID` varchar(50) default NULL
) TYPE=MyISAM;
