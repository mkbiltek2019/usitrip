<?php
/*
  $Id: admin_members.php,v 1.2 2004/03/05 00:36:41 ccwjr Exp $

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

if ($HTTP_GET_VARS['gID']) {
  define('HEADING_TITLE', 'Admin Groups');
} elseif ($HTTP_GET_VARS['gPath']) {
  define('HEADING_TITLE', 'Define Groups');
} else {
  define('HEADING_TITLE', 'Admin Members');
}

define('TEXT_COUNT_GROUPS', 'Groups: ');

define('TABLE_HEADING_NAME', 'Name');
define('TABLE_HEADING_EMAIL', 'Email Address');
define('TABLE_HEADING_PUBLICITY_NAME', 'Public Name');
define('TABLE_HEADING_PASSWORD', 'Password');
define('TABLE_HEADING_CONFIRM', 'Confirm Password');
define('TABLE_HEADING_GROUPS', 'Groups Level');
define('TABLE_HEADING_CREATED', 'Account Created');
define('TABLE_HEADING_MODIFIED', 'Account Modified');
define('TABLE_HEADING_LOGDATE', 'Last Access');
define('TABLE_HEADING_LOGNUM', 'LogNum');
define('TABLE_HEADING_LOG_NUM', 'Log Number');
define('TABLE_HEADING_ACTION', 'Action');

define('TABLE_HEADING_GROUPS_NAME', 'Groups Name');
define('TABLE_HEADING_GROUPS_DEFINE', 'Boxes and Files Selection');
define('TABLE_HEADING_GROUPS_GROUP', 'Level');
define('TABLE_HEADING_GROUPS_CATEGORIES', 'Categories Permission');


define('TEXT_INFO_HEADING_DEFAULT', 'Admin Member ');
define('TEXT_INFO_HEADING_DELETE', 'Delete Permission ');
define('TEXT_INFO_HEADING_EDIT', 'Edit Category / ');
define('TEXT_INFO_HEADING_NEW', 'New Admin Member ');

define('TEXT_INFO_DEFAULT_INTRO', 'Member Group');
define('TEXT_INFO_DELETE_INTRO', 'Remove <nobr><b>%s</b></nobr> from <nobr>Admin Members?</nobr>');
define('TEXT_INFO_DELETE_INTRO_NOT', 'You can not delete <nobr>%s group!</nobr>');
define('TEXT_INFO_EDIT_INTRO', 'Set permission level here: ');

define('TEXT_INFO_FULLNAME', 'Name: ');
define('TEXT_INFO_FIRSTNAME', '�գ�');
define('TEXT_INFO_LASTNAME', '����');
define('TEXT_INFO_PUBLICITY_NAME', '<b>Public Name: </b>');
define('TEXT_INFO_EMAIL', 'Email Address: ');
define('TEXT_INFO_PASSWORD', 'Password: ');
define('TEXT_INFO_CONFIRM', 'Confirm Password: ');
define('TEXT_INFO_CREATED', 'Account Created: ');
define('TEXT_INFO_MODIFIED', 'Account Modified: ');
define('TEXT_INFO_LOGDATE', 'Last Access: ');
define('TEXT_INFO_LOGNUM', 'Log Number: ');
define('TEXT_INFO_GROUP', 'Group Level: ');
define('TEXT_INFO_SOURCE', 'Source: ');

define('TEXT_INFO_ERROR', '<font color="red">Email address has already been used! Please try again.</font>');

define('JS_ALERT_FIRSTNAME', '- Required: Firstname \n');
define('JS_ALERT_LASTNAME', '- Required: Lastname \n');
define('JS_ALERT_EMAIL', '- Required: Email address \n');
define('JS_ALERT_EMAIL_FORMAT', '- Email address format is invalid! \n');
define('JS_ALERT_EMAIL_USED', '- Email address has already been used! \n');
define('JS_ALERT_LEVEL', '- Required: Group Member \n');

define('ADMIN_EMAIL_SUBJECT', 'New Admin Member');
define('ADMIN_EMAIL_TEXT', 'Hi %s,' . "\n\n" . 'You can access the admin panel with the following password. Once you access the admin, please change your password!' . "\n\n" . 'Website : %s' . "\n" . 'Username: %s' . "\n" . 'Password: %s' . "\n\n" . 'Thanks!' . "\n" . '%s' . "\n\n" . 'This is a system automated response, please do not reply, as it would be unread!');
define('ADMIN_EMAIL_EDIT_SUBJECT', 'Admin Member Profile Edit');
define('ADMIN_EMAIL_EDIT_TEXT', 'Hi %s,' . "\n\n" . 'Your personal information has been updated by an administrator.' . "\n\n" . 'Website : %s' . "\n" . 'Username: %s' . "\n" . 'Password: %s' . "\n\n" . 'Thanks!' . "\n" . '%s' . "\n\n" . 'This is a system automated response, please do not reply, as it would be unread!');

define('TEXT_INFO_HEADING_DEFAULT_GROUPS', 'Admin Group ');
define('TEXT_INFO_HEADING_DELETE_GROUPS', 'Delete Group ');

define('TEXT_INFO_DEFAULT_GROUPS_INTRO', '<b>NOTE:</b><li><b>edit:</b> edit group name.</li><li><b>delete:</b> delete group.</li><li><b>define:</b> define and change group access.</li>');
define('TEXT_INFO_DELETE_GROUPS_INTRO', 'It will also delete members of this group. Are you sure want to delete <nobr><b>%s</b> group?</nobr>');
define('TEXT_INFO_DELETE_GROUPS_INTRO_NOT', 'You can not delete this groups!');
define('TEXT_INFO_GROUPS_INTRO', 'Give an unique group name. Click next to submit.');

define('TEXT_INFO_HEADING_GROUPS', 'New Group');
define('TEXT_INFO_GROUPS_NAME', ' <b>Group Name:</b><br>Give an unique group name. Then, click next to submit.<br>');
define('TEXT_INFO_GROUPS_NAME_FALSE', '<font color="red"><b>ERROR:</b> The group name must have at least 5 characters!</font>');
define('TEXT_INFO_GROUPS_NAME_USED', '<font color="red"><b>ERROR:</b> Group name has already been used!</font>');
define('TEXT_INFO_GROUPS_LEVEL', 'Group Level: ');
define('TEXT_INFO_GROUPS_BOXES', '<b>Boxes Permission:</b><br>Give access to selected boxes.');
define('TEXT_INFO_GROUPS_BOXES_INCLUDE', 'Include files stored in: ');

define('TEXT_INFO_EDIT_GROUP_INTRO', 'Edit Group Name: ');

define('TEXT_INFO_HEADING_DEFINE', 'Define Group');
if ($HTTP_GET_VARS['gPath'] == 1) {
  define('TEXT_INFO_DEFINE_INTRO', '<b>%s :</b><br>You can not change file permission for this group.<br><br>');
} else {
  define('TEXT_INFO_DEFINE_INTRO', '<b>%s :</b><br>Change permission for this group by selecting or unselecting boxes and files provided. Click <b>save</b> to save the changes.<br><br>');
}
?>
