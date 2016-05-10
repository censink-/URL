SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

CREATE TABLE `bans` (
`id` int(11) NOT NULL,
  `ip` varchar(55) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ban_by` int(11) NOT NULL,
  `unban_by` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

CREATE TABLE `logins` (
`id` int(11) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `captcha` tinyint(1) NOT NULL,
  `password` int(1) NOT NULL,
  `ip` varchar(55) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


CREATE TABLE `settings` (
  `setting` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO `settings` (`setting`, `value`) VALUES
('password', 'k81382bb8f338c6cc5e8f3e15129b975bdbca886dm8'),
('default_url', 'http://steamblock.net/index.php'),
('base_url', 'http://steamblock.net/url/');

CREATE TABLE `urls` (
`id` int(11) NOT NULL,
  `url` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

CREATE TABLE `visits` (
`id` int(11) NOT NULL,
  `ip` varchar(16) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `url_id` int(11) NOT NULL
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

ALTER TABLE `bans`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `logins`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `urls`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `visits`
 ADD PRIMARY KEY (`id`);

ALTER TABLE `bans`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

ALTER TABLE `logins`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

ALTER TABLE `urls`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;

ALTER TABLE `visits`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=1;