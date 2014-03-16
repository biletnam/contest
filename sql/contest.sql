-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Сен 10 2009 г., 02:12
-- Версия сервера: 5.0.51
-- Версия PHP: 5.2.6

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `contest`
--

-- --------------------------------------------------------

--
-- Структура таблицы `contest`
--

CREATE TABLE IF NOT EXISTS `contest` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  `name_translit` varchar(128) NOT NULL,
  `rules` text NOT NULL,
  `desc` text NOT NULL COMMENT 'описание',
  `content` text NOT NULL,
  `down_count` int(10) NOT NULL default '0',
  `mktime` timestamp NULL default CURRENT_TIMESTAMP,
  `state` enum('nominate','open','wait','vote','close') NOT NULL default 'open' COMMENT 'номинант,открыт,ожидание,голосование,закрыт',
  `open_date` datetime NOT NULL,
  `wait_date` datetime NOT NULL,
  `vote_date` datetime NOT NULL,
  `close_date` datetime NOT NULL,
  `auto_tick` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `name_translit` (`name_translit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='конкурс' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `contest`
--


-- --------------------------------------------------------

--
-- Структура таблицы `membership`
--

CREATE TABLE IF NOT EXISTS `membership` (
  `id` int(10) NOT NULL auto_increment,
  `team` int(10) NOT NULL,
  `user` int(10) NOT NULL,
  `role` int(10) NOT NULL,
  `state` enum('accept','candidate','invite','decline','leave') default 'candidate' COMMENT 'состояние (принят,кандидат,приглашён,отклонён,покинул)',
  `mktime` timestamp NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='состав группы' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `membership`
--


-- --------------------------------------------------------

--
-- Структура таблицы `participation`
--

CREATE TABLE IF NOT EXISTS `participation` (
  `id` int(10) NOT NULL auto_increment,
  `contest` int(10) NOT NULL,
  `contest_name` varchar(128) default NULL,
  `user` int(10) NOT NULL,
  `user_name` varchar(128) default NULL,
  `team` int(10) default NULL,
  `team_name` varchar(128) default NULL,
  `role` int(10) default NULL,
  `role_name` varchar(128) default NULL,
  `mktime` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'начало участия',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='участие в конкурсе' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `participation`
--


-- --------------------------------------------------------

--
-- Структура таблицы `role`
--

CREATE TABLE IF NOT EXISTS `role` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  `desc` text NOT NULL COMMENT 'описание',
  `mktime` timestamp NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='роль участника в группе' AUTO_INCREMENT=4 ;

--
-- Дамп данных таблицы `role`
--

INSERT INTO `role` (`id`, `name`, `desc`, `mktime`) VALUES
(1, 'team_leader', 'лидер группы, имеет права редактировать, удалять и голосовать от имени группы', '2009-08-07 23:52:24'),
(2, 'member', 'обычный участник', '2009-08-07 23:52:24'),
(3, 'viewer', 'зритель', '2009-08-29 11:51:35');

-- --------------------------------------------------------

--
-- Структура таблицы `team`
--

CREATE TABLE IF NOT EXISTS `team` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  `name_translit` varchar(128) NOT NULL,
  `desc` text NOT NULL COMMENT 'описание',
  `mktime` timestamp NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `name_translit` (`name_translit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='группа' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `team`
--


-- --------------------------------------------------------

--
-- Структура таблицы `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  `name_translit` varchar(128) NOT NULL,
  `passw` varchar(32) NOT NULL,
  `mktime` timestamp NULL default CURRENT_TIMESTAMP,
  `time_zone` varchar(128) NOT NULL default 'Europe/Moscow',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`),
  UNIQUE KEY `name_translit` (`name_translit`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='участник' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `user`
--


-- --------------------------------------------------------

--
-- Структура таблицы `vote`
--

CREATE TABLE IF NOT EXISTS `vote` (
  `id` int(10) NOT NULL auto_increment,
  `contest` int(10) NOT NULL,
  `from` int(10) NOT NULL,
  `to` int(10) NOT NULL,
  `value` int(10) NOT NULL,
  `desc` text COMMENT 'комментарий',
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='голосование' AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `vote`
--


-- --------------------------------------------------------

--
-- Структура таблицы `work`
--

CREATE TABLE IF NOT EXISTS `work` (
  `id` int(10) NOT NULL auto_increment,
  `name` varchar(128) NOT NULL,
  `filename` text NOT NULL,
  `pid` int(10) NOT NULL,
  `mktime` timestamp NOT NULL default CURRENT_TIMESTAMP,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Дамп данных таблицы `work`
--

