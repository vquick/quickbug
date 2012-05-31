
SET NAMES utf8;

CREATE TABLE IF NOT EXISTS `quickbug_bugs` (
  `bugid` int(11) NOT NULL auto_increment,
  `projectid` int(11) NOT NULL default '0',
  `verid` int(11) NOT NULL default '0',
  `moduleid` int(11) NOT NULL default '0',
  `subject` varchar(250) default '',
  `info` text,
  `groupid` int(11) NOT NULL default '0',
  `createuid` int(11) NOT NULL default '0',
  `touserid` int(11) NOT NULL default '0',
  `severity` tinyint(4) default '1',
  `frequency` tinyint(4) default '1',
  `priority` tinyint(4) default '3',
  `bugtype` tinyint(4) default '1',
  `status` tinyint(4) default '1',
  `savetype` tinyint(4) default '1',
  `dateline` int(11) default '0',
  `lastuptime` int(11) NOT NULL default '0',
  PRIMARY KEY  (`bugid`),
  KEY `projectid` (`projectid`),
  KEY `verid` (`verid`),
  KEY `moduleid` (`moduleid`),
  KEY `createuid` (`createuid`),
  KEY `touserid` (`touserid`),
  KEY `severity` (`severity`),
  KEY `status` (`status`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `quickbug_bug_comment` (
  `commentid` int(11) NOT NULL auto_increment,
  `bugid` int(11) default '0',
  `userid` int(11) default '0',
  `username` varchar(100) default '',
  `info` text,
  `dateline` int(11) default '0',
  PRIMARY KEY  (`commentid`),
  KEY `bugid` (`bugid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `quickbug_bug_docs` (
  `bugdocid` int(11) NOT NULL auto_increment,
  `bugid` int(11) default '0',
  `docname` varchar(100) default '',
  `docfile` varchar(200) default '',
  `dateline` int(11) default '0',
  PRIMARY KEY  (`bugdocid`),
  KEY `bugid` (`bugid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `quickbug_bug_history` (
  `id` int(11) NOT NULL auto_increment,
  `bugid` int(11) NOT NULL default '0',
  `historydata` text,
  `dateline` int(11) default '0',
  PRIMARY KEY  (`id`),
  KEY `bugid` (`bugid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `quickbug_bug_invite` (
  `inviteid` int(11) NOT NULL auto_increment,
  `bugid` int(11) default '0',
  `userid` int(11) default '0',
  `ivtuserid` int(11) default '0',
  `opt` tinyint(4) default '0',
  `isread` tinyint(4) default '0',
  `dateline` int(11) default '0',
  PRIMARY KEY  (`inviteid`),
  KEY `bugid` (`bugid`),
  KEY `ivtuserid` (`ivtuserid`,`isread`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `quickbug_bug_tpls` (
  `id` int(11) NOT NULL auto_increment,
  `userid` int(11) NOT NULL default '0',
  `tplname` varchar(100) default '',
  `tplhtml` text,
  PRIMARY KEY  (`id`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `quickbug_group` (
  `groupid` int(11) NOT NULL auto_increment,
  `groupname` varchar(100) default '',
  `info` varchar(100) default '',
  `createuid` int(11) default '0',
  `grouptype` tinyint default '0',
  `dateline` int(11) default '0',
  PRIMARY KEY  (`groupid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `quickbug_grouppriv` (
  `gpid` int(11) NOT NULL auto_increment,
  `groupid` int(11) default '0',
  `priv` text,
  PRIMARY KEY  (`gpid`),
  UNIQUE KEY `groupid` (`groupid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `quickbug_groupuser` (
  `guid` int(11) NOT NULL auto_increment,
  `groupid` int(11) default '0',
  `userid` int(11) default '0',
  PRIMARY KEY  (`guid`),
  KEY `groupid` (`groupid`),
  KEY `groupid_2` (`groupid`,`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `quickbug_operate_history` (
  `id` int(11) NOT NULL auto_increment,
  `bugid` int(11) NOT NULL default '0',
  `userid` int(11) NOT NULL default '0',
  `text` text,
  `dateline` int(11) default '0',
  PRIMARY KEY  (`id`),
  KEY `bugid` (`bugid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `quickbug_project` (
  `projectid` int(11) NOT NULL auto_increment,
  `projectname` varchar(100) default '',
  `info` text,
  `userid` int(11) default '0',
  `dateline` int(11) default '0',
  PRIMARY KEY  (`projectid`),
  KEY `userid` (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `quickbug_project_docs` (
  `pjdocid` int(11) NOT NULL auto_increment,
  `projectid` int(11) default '0',
  `docname` varchar(100) default '',
  `docfile` varchar(200) default '',
  `docsize` int(11) default '0',
  `dateline` int(11) default '0',
  PRIMARY KEY  (`pjdocid`),
  KEY `projectid` (`projectid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `quickbug_project_modules` (
  `moduleid` int(11) NOT NULL auto_increment,
  `projectid` int(11) default '0',
  `modulename` varchar(100) default '',
  `dateline` int(11) default '0',
  PRIMARY KEY  (`moduleid`),
  KEY `projectid` (`projectid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `quickbug_project_vers` (
  `verid` int(11) NOT NULL auto_increment,
  `projectid` int(11) default '0',
  `vername` varchar(100) default '',
  `dateline` int(11) default '0',
  PRIMARY KEY  (`verid`),
  KEY `projectid` (`projectid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;



CREATE TABLE IF NOT EXISTS `quickbug_user` (
  `userid` int(11) NOT NULL auto_increment,
  `username` varchar(100) default '',
  `truename` varchar(100) default '',
  `passwd` varchar(100) default '',
  `email` varchar(100) default '',
  `usertype` tinyint(4) default '1',
  `priv` tinyint(4) default '1',
  `enable` tinyint(4) default '1',
  `dateline` int(11) default '0',
  `ext` text,
  PRIMARY KEY  (`userid`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
