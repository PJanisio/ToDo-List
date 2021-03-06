<?php
if(!defined("IN_MYBB")) {
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

global $cache;
if(!isset($pluginlist))
	$pluginlist = $cache->read("plugins");

//WIO Hooks
$plugins->add_hook("fetch_wol_activity_end", "todo_wol_activity");
$plugins->add_hook("build_friendly_wol_location_end", "todo_wol_location");
//ACP Hooks
if(is_array($pluginlist['active']) && in_array("mybbservice", $pluginlist['active'])) {
	$plugins->add_hook("mybbservice_actions", "todo_mybbservice_actions");
	$plugins->add_hook("mybbservice_permission", "todo_admin_config_permissions");
} else {
	$plugins->add_hook("admin_config_menu", "todo_admin_config_menu");
	$plugins->add_hook("admin_config_action_handler", "todo_admin_config_action_handler");
	$plugins->add_hook("admin_config_permissions", "todo_admin_config_permissions");
}

function todolist_info()
{
	return array(
		"name"			=> "ToDo-Liste (+)",
		"description"	=> "Dieses Plugin erstellt eine ToDo Liste, mithilfe Aufgaben in deinem Forum verwaltet werden können<br /><i>Based on ToDo List by FalkenaugeMihawk</i>",
		"website"		=> "http://mybbservice.de",
		"author"		=> "MyBBService / Pavlus",
		"authorsite"	=> "http://mybbservice.de",
		"version"		=> "1.0.3",
		"guid"			=> "",
		"compatibility" => "17*,18*",
		"dlcid"			=> "18"
	);
}


function todolist_install()
{
	global $db, $lang;
	$lang->load('todolist');


	//Datenbank Tabelle
	$col = $db->build_create_table_collation();
	$db->query("CREATE TABLE `".TABLE_PREFIX."todolist` (
				`id`			int(11)			NOT NULL AUTO_INCREMENT,
				`pid`			int(11)			NOT NULL,
				`title`			varchar(50)		NOT NULL,
				`message`		text			NOT NULL,
				`name`			varchar(120)	NOT NULL,
				`nameid`		int(10)			NOT NULL,
				`date`			bigint(30)		NOT NULL,
				`assign`		int(10)			NOT NULL DEFAULT '0',
				`lasteditor`	varchar(120)	NOT NULL DEFAULT '',
				`lasteditorid`	int(10)			NOT NULL DEFAULT '0',
				`lastedit`		bigint(30)		NOT NULL DEFAULT '0',
				`priority`		varchar(6)		NOT NULL DEFAULT 'normal',
				`status`		varchar(11)		NOT NULL DEFAULT 'wait',
				`done`			int(3)			NOT NULL DEFAULT '0',
				`version`		varchar(11)		NOT NULL DEFAULT '',
	PRIMARY KEY (`id`) ) ENGINE=MyISAM {$col}");

	$db->query("CREATE TABLE `".TABLE_PREFIX."todolist_projects` (
				`id`			int(11)			NOT NULL AUTO_INCREMENT,
				`title`			varchar(50)		NOT NULL,
				`description`	text			NOT NULL,
	PRIMARY KEY (`id`) ) ENGINE=MyISAM {$col}");

	$db->query("CREATE TABLE `".TABLE_PREFIX."todolist_permissions` (
				`pid`			int(11)			NOT NULL,
				`gid`			int(11)			NOT NULL,
				`can_see`		boolean			NOT NULL,
				`can_add`		boolean			NOT NULL,
				`can_edit`		boolean			NOT NULL)
	ENGINE=MyISAM {$col}");

	$db->query("CREATE TABLE `".TABLE_PREFIX."todolist_searchs` (
				`id`			int(11)			NOT NULL AUTO_INCREMENT,
				`title`			varchar(50)		NOT NULL,
				`url`			text			NOT NULL,
	PRIMARY KEY (`id`) ) ENGINE=MyISAM {$col}");

	//Template Gruppe
	$templateset = array(
		"prefix" => "todolist",
		"title" => "ToDoListe",
	);
	$db->insert_query("templategroups", $templateset);


	//Templates
	$templatearray = array(
		"title" => "todolist",
		"template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$lang->title_overview}: {\$mybb->settings[\'todo_name\']}</title>
{\$headerinclude}
</head>
<body>
{\$header}
{\$multipage}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=\"thead\" colspan=\"6\"><strong>{\$lang->title_overview}: {\$mybb->settings[\'todo_name\']}</strong></td>
		<td class=\"thead\" colspan=\"2\" style=\"text-align: right;\"><a href=\"todolist.php?action=new\">{\$lang->new}</a> | <a href=\"todolist.php?action=search\">{\$lang->search}</a></td>
	</tr>
	<tr>
		<td class=tcat>{\$lang->title_todo}</td>
		<td class=tcat>{\$lang->date_todo}</td>
		<td class=tcat>{\$lang->from_todo}</td>
		<td class=tcat>{\$lang->priority_todo}</td>
		<td class=tcat>{\$lang->status_todo}</td>
		<td class=tcat>{\$lang->done_todo}</td>
		<td class=tcat>{\$lang->assign_todo}</td>
		<td class=tcat style=\"width:300px;\">{\$lang->action_todo}</td>
	</tr>
	{\$todo}
	<tr class=\"trow1\">
		<td colspan=\"8\">{\$addtodo}</td>
	</tr>
</table>
{\$multipage}
<br />
{\$footer}
</body>
</html>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_projects",
		"template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$lang->title_overview}: {\$mybb->settings[\'todo_name\']}</title>
{\$headerinclude}
</head>
<body>
{\$header}
{\$multipage}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=\"thead\" colspan=\"2\"><strong>{\$lang->title_overview}: {\$mybb->settings[\'todo_name\']}</strong></td>
		<td class=\"thead\" style=\"text-align: right;\"><a href=\"todolist.php?action=new\">{\$lang->new}</a> | <a href=\"todolist.php?action=search\">{\$lang->search}</a></td>
	</tr>
	<tr>
		<td class=tcat>{\$lang->title_todo}</td>
		<td class=tcat>{\$lang->description_todo}</td>
		<td class=tcat>{\$lang->done_todo}</td>
	</tr>
	{\$todo}
</table>
{\$multipage}
<br />
{\$footer}
</body>
</html>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_show",
		"template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$mybb->settings[\'todo_name\']} - {\$lang->show_showtodo}</title>
{\$headerinclude}
</head>
<body>
{\$header}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=thead colspan=\"4\"><strong>{\$lang->title_overview}: {\$mybb->settings[\'todo_name\']} - {\$lang->show_showtodo}</strong></td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:200px;\">{\$lang->title_todo}:</td>
		<td>{\$row[\'title\']}</td>
	</tr>
	<tr class=\"trow2\">
		<td style=\"width:200px;\">{\$lang->date_todo}:</td>
		<td>{\$date}</td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:200px;\">{\$lang->from_todo}:</td>
		<td>{\$from}</td>
	</tr>
	<tr class=\"trow2\">
		<td style=\"width:200px;\">{\$lang->assign_todo}:</td>
		<td>{\$assign}</td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:200px;\">{\$lang->priority_todo}:</td>
		<td>{\$priority}</td>
	</tr>
	<tr class=\"trow2\">
		<td style=\"width:200px;\">{\$lang->done_todo}:</td>
		<td>{\$done}</td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:200px;\">{\$lang->status_todo}:</td>
		<td>{\$status}</td>
	</tr>
	<tr class=\"trow2\">
		<td style=\"width:200px;\">{\$lang->version_todo}:</td>
		<td>{\$row[\'version\']}</td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:200px;\">{\$lang->description_todo}:</td>
		<td>{\$message}</td>
	</tr>
	{\$mod_todo}
	{\$lastedit}
	<tr class=\"trow2\">
		<td colspan=\"2\">{\$back}</td>
	</tr>
</table>
<br />
{\$footer}
</body>
</html>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_add",
		"template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$mybb->settings[\'todo_name\']} - {\$lang->add_todo}</title>
{\$headerinclude}
</head>
<body>
{\$header}
{\$errors}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=thead colspan=2><strong>{\$lang->title_overview}: {\$lang->add_todo}</strong></td>
	</tr>
	<form action=\"todolist.php\" method=\"post\">
	<input type=\"hidden\" name=\"action\" value=\"add\" />
	<input type=\"hidden\" name=\"my_post_key\" value=\"{\$mybb->post_code}\" />
	<input type=\"hidden\" name=\"pid\" value=\"{\$mybb->input[\'pid\']}\" />
	<tr class=\"trow1\">
		<td style=\"width:100px;\">{\$lang->title_todo}:</td>
		<td><input type=\"text\" class=\"textbox\" name=\"title\" style=\"width:300px;\" value=\"{\$title}\" /></td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:100px;\">{\$lang->priority_todo}:</td>
		<td><select name=\"priority\" style=\"width:100px;\">
			<option value=\"normal\" style=\"background-image:url(images/todolist/norm_prio.png); background-repeat:no-repeat; text-align:center; \" {\$priority_check[\'normal\']}>{\$lang->normal_priority}</option>
			<option value=\"high\" style=\"background-image:url(images/todolist/high_prio.gif); background-repeat:no-repeat; text-align:center; \" {\$priority_check[\'high\']}>{\$lang->high_priority}</option>
			<option value=\"low\" style=\"background-image:url(images/todolist/low_prio.gif); background-repeat:no-repeat; text-align:center; \" {\$priority_check[\'low\']}>{\$lang->low_priority}</option>
		</select></td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:100px;\">{\$lang->assign_todo}:</td>
		<td><select name=\"assign\" style=\"width:100px;\">{\$userselect}</select></td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:100px;\">{\$lang->version_todo}:</td>
		<td><input type=\"text\" class=\"textbox\" name=\"version\" value=\"{\$version}\" /></td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width:200px;\">{\$lang->description_todo}:</td>
		<td><textarea name=\"message\" rows=\"20\" cols=\"70\" id=\"message\">----------------{\$mybb->user['username']} @ [ {\$lang->current_time}]----------------{\$message}</textarea>{\$codebuttons}</td>
	</tr>
	<tr class=\"trow1\">
		<td colspan=\"2\"><input type=\"submit\" value=\"{\$lang->add_todo}\" style=\"margin-left: 280px; \"/></td>
	</tr>
	</form>
</table>
{\$footer}
</body>
</html>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_edit",
		"template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$mybb->settings[\'todo_name\']} - {\$lang->edit_edittodo}</title>
{\$headerinclude}
</head>
<body>
{\$header}
{\$errors}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=\"thead\" colspan=\"8\"><strong>{\$lang->title_overview}: {\$lang->edit_edittodo}</strong></td>
	</tr>
	<form action=\"todolist.php?id={\$id}\" method=\"post\">
	<input type=\"hidden\" name=\"action\" value=\"edit\" />
	<input type=\"hidden\" name=\"my_post_key\" value=\"{\$mybb->post_code}\" />
		<tr class=\"trow1\">
			<td style=\"width:100px;\">Titel:</td>
			<td><input type=\"text\" class=\"textbox\" name=\"title\" size=\"40\" value=\"{\$title}\"></td>
		</tr>
		<tr class=\"trow1\">
			<td style=\"width:100px;\">{\$lang->priority_todo}:</td>
			<td>{\$lang->nowprio_edittodo}: {\$priority} - 
				<select name=\"priority\" style=\"width:100px;\">
					<option value=\"high\" style=\"background-image:url(images/todolist/high_prio.gif); background-repeat:no-repeat; text-align:center;\" {\$priority_check[\'high\']}>{\$lang->high_priority}</option>
					<option value=\"normal\" style=\"background-image:url(images/todolist/norm_prio.png); background-repeat:no-repeat; text-align:center;\" {\$priority_check[\'normal\']}>{\$lang->normal_priority}</option>
					<option value=\"low\" style=\"background-image:url(images/todolist/low_prio.gif); background-repeat:no-repeat; text-align:center;\" {\$priority_check[\'low\']}>{\$lang->low_priority}</option>
				</select>
			</td>
		</tr>
		<tr class=\"trow1\">
			<td style=\"width:100px;\">{\$lang->done_todo}:</td>
			<td>{\$lang->nowdone_edittodo}: {\$done} -
				<select name=\"done\" style=\"width:130px;\">
					<option value=\"0\" style=\"background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center;\" {\$done_check[\'0\']}>{\$lang->done_0}</option>
					<option value=\"25\" style=\"background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center;\" {\$done_check[\'25\']}>{\$lang->done_25}</option>
					<option value=\"50\" style=\"background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center;\" {\$done_check[\'50\']}>{\$lang->done_50}</option>
					<option value=\"75\" style=\"background-image:url(images/spinner.gif); background-repeat:no-repeat; text-align:center;\" {\$done_check[\'75\']}>{\$lang->done_75}</option>
					<option value=\"100\" style=\"background-image:url(images/todolist/done.png); background-repeat:no-repeat; text-align:center;\" {\$done_check[\'100\']}>{\$lang->done_100}</option>
				</select>			
			</td>
		</tr>
		<tr class=\"trow1\">
			<td style=\"width:100px;\">{\$lang->status_todo}:</td>
			<td>{\$lang->nowstat_edittodo}: {\$status} - 
				<select name=\"status\" style=\"width:140px;\">
					<option value=\"wait\" style=\"background-image:url(images/todolist/waiting.png); background-repeat:no-repeat; text-align:center;\" {\$status_check[\'wait\']}>{\$lang->status_wait}</option>
					<option value=\"development\" style=\"background-image:url(images/todolist/development.png); background-repeat:no-repeat; text-align:center;\" {\$status_check[\'development\']}>{\$lang->status_dev}</option>
					<option value=\"resolved\" style=\"background-image:url(images/icons/exclamation.gif); background-repeat:no-repeat; text-align:center;\" {\$status_check[\'resolved\']}>{\$lang->status_resolved}</option>
					<option value=\"feedback\" style=\"background-image:url(images/icons/feedback.gif); background-repeat:no-repeat; text-align:center;\" {\$status_check[\'feedback\']}>{\$lang->status_feed}</option>
					<option value=\"closed\" style=\"background-image:url(images/todolist/lock.png); background-repeat:no-repeat; text-align:center;\" {\$status_check[\'closed\']}>{\$lang->status_closed}</option>
				</select>
			</td>
		</tr>
		<tr class=\"trow1\">
			<td style=\"width:100px;\">{\$lang->assign_todo}:</td>
			<td><select name=\"assign\" style=\"width:100px;\">{\$userselect}</select></td>
		</tr>
		<tr class=\"trow1\">
			<td style=\"width:100px;\">{\$lang->version_todo}:</td>
			<td><input type=\"text\" class=\"textbox\" name=\"version\" value=\"{\$version}\" /></td>
		</tr>
		<tr class=\"trow1\">
			<td style=\"width:200px;\">{\$lang->description_todo}:</td>
			<td><textarea name=\"message\" rows=\"20\" cols=\"70\" id=\"message\">{\$message}----------------{\$mybb->user['username']} @ [ {\$lang->current_time}]----------------</textarea>{\$codebuttons}</td>
		</tr>
		<tr class=\"trow1\">
			<td colspan=\"2\"><input type=\"submit\" value=\"{\$lang->send_edittodo}\" style=\"margin-left: 280px; \"/></td>
		</tr>
		<tr class=\"trow1\">
			<td colspan=\"2\"><a href=\"todolist.php?action=show&id={\$id}\">{\$lang->back_showtodo}</a></td>
		</tr>
	</form>
</table>
{\$footer}
</body>
</html>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_table",
		"template" => "<tr class=\"trow1\" colspan=\"8\">
	<td>{\$row[\'title\']}</td>
	<td>{\$date}</td>
	<td>{\$owner}</td>
	<td>{\$priority}</td>
	<td>{\$status}</td>
	<td>{\$done}</td>
	<td>{\$assign}</td>
	<td style=\"width:200px\">
		<center>
			<a href=\"todolist.php?action=show&id={\$row[\'id\']}\"><img src=\"images/todolist/show.png\" /> {\$lang->show_todo}</a> {\$mod_todo}</a>
		</center>
	</td>
</tr>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_table_no_results",
		"template" => "<tr class=\"trow1\">
	<td colspan=\"8\"><center>{\$lang->no_todo}</center></td>
</tr>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_projects_table",
		"template" => "<tr class=\"trow1\" colspan=\"8\">
	<td><a href=\"todolist.php?action=show_project&id={\$row[\'id\']}\">{\$row[\'title\']}</a></td>
	<td>{\$row[\'description\']}</td>
	<td>{\$done}</td>
</tr>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_projects_table_no_results",
		"template" => "<tr class=\"trow1\">
	<td colspan=\"3\"><center>{\$lang->no_projects}</center></td>
</tr>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_mod",
		"template" => "<a href=\"todolist.php?action=edit&id={\$row[\'id\']}\"><img src=\"images/todolist/edit.png\" /> {\$lang->edit_todo}</a> 
- <a href=\"todolist.php?action=delete&id={\$row[\'id\']}\"><img src=\"images/todolist/delete.png\" /> {\$lang->delete_todo}</a>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_mod_table",
		"template" => "<tr class=\"trow2\">
	<td style=\"width:100px;\">{\$lang->action_todo}</td>
	<td>{\$mod_todo}</td>
</tr>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_edited",
		"template" => "<tr class=\"trow1\">
	<td style=\"width:200px;\">{\$lang->lastedit_showtodo}:</td>
	<td>{\$date} {\$lang->from_todo} {\$lasteditor}</td>
</tr>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_search",
		"template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$lang->title_overview}: {\$mybb->settings[\'todo_name\']}</title>
{\$headerinclude}
<link rel=\"stylesheet\" href=\"{\$mybb->asset_url}/jscripts/select2/select2.css\">
<script type=\"text/javascript\" src=\"{\$\mybb->asset_url}/jscripts/select2/select2.min.js\"></script>
</head>
<body>
{\$header}
{\$results}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=\"thead\" colspan=\"8\"><strong>{\$lang->search}</strong></td>
	</tr>
	<form action=\"todolist.php?action=search\" method=\"post\">
	<input type=\"hidden\" name=\"search\" value=\"do\" />
	<input type=\"hidden\" name=\"my_post_key\" value=\"{\$mybb->post_code}\" />
	<tr class=\"trow1\">
		<td style=\"width: 10%;\">{\$lang->search_string}:</td>
		<td><input type=\"text\" value=\"{\$string}\" class=\"textbox\" name=\"string\" /></td>
		<td style=\"width: 10%;\">{\$lang->search_status}:</td>
		<td><select name=\"status[]\" multiple=\"multiple\">
				<option value=\"wait\" style=\"background-image:url(images/todolist/waiting.png); background-repeat:no-repeat; text-align:center;\" {\$status_check[\'wait\']}>{\$lang->status_wait}</option>
				<option value=\"development\" style=\"background-image:url(images/todolist/development.png); background-repeat:no-repeat; text-align:center;\" {\$status_check[\'development\']}>{\$lang->status_dev}</option>
				<option value=\"resolved\" style=\"background-image:url(images/icons/exclamation.gif); background-repeat:no-repeat; text-align:center;\" {\$status_check[\'resolved\']}>{\$lang->status_resolved}</option>
				<option value=\"feedback\" style=\"background-image:url(images/icons/feedback.gif); background-repeat:no-repeat; text-align:center;\" {\$status_check[\'feedback\']}>{\$lang->status_feed}</option>
				<option value=\"closed\" style=\"background-image:url(images/todolist/lock.png); background-repeat:no-repeat; text-align:center;\" {\$status_check[\'closed\']}>{\$lang->status_closed}</option>
			</select></td>
	</tr>
	<tr class=\"trow2\">
		<td style=\"width: 10%;\">{\$lang->search_creator}:</td>
		<td><input type=\"text\" value=\"{\$creator}\" class=\"textbox\" name=\"creator\" id=\"creator\" /></td>
		<td style=\"width: 10%;\">{\$lang->search_assign}:</td>
		<td><input type=\"text\" value=\"{\$assign}\" class=\"textbox\" name=\"assign\" id=\"assign\" /></td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"width: 10%;\">{\$lang->search_project}:</td>
		<td><select name=\"project[]\" multiple=\"multiple\">{\$projects}</select></td>
		<td style=\"width: 10%;\">{\$lang->search_priority}:</td>
		<td><select name=\"priority[]\" multiple=\"multiple\">
					<option value=\"high\" style=\"background-image:url(images/todolist/high_prio.gif); background-repeat:no-repeat; text-align:center;\" {\$priority_check[\'high\']}>{\$lang->high_priority}</option>
					<option value=\"normal\" style=\"background-image:url(images/todolist/norm_prio.png); background-repeat:no-repeat; text-align:center;\" {\$priority_check[\'normal\']}>{\$lang->normal_priority}</option>
					<option value=\"low\" style=\"background-image:url(images/todolist/low_prio.gif); background-repeat:no-repeat; text-align:center;\" {\$priority_check[\'low\']}>{\$lang->low_priority}</option>
			</select></td>
	</tr>
	<tr class=\"trow2\">
		<td style=\"width: 10%;\">{\$lang->version_todo}:</td>
		<td><input type=\"text\" class=\"textbox\" name=\"version\" value=\"{\$version}\" /></td>
		<td></td>
		<td></td>
	</tr>
	<tr class=\"trow1\">
		<td colspan=\"8\" style=\"text-align: center;\"><input type=\"submit\" value=\"{\$lang->search_do}\" /></td>
	</tr>
	</form>
</table>
{\$searches}
{\$footer}
<script type=\"text/javascript\">
<!--
	if(use_xmlhttprequest == \"1\")
	{
		$(\"#creator\").select2({
			minimumInputLength: 3,
			maximumSelectionSize: 3,
			ajax: {
				url: \"xmlhttp.php?action=get_users\",
				dataType: \'json\',
				data: function (term, page) {
					return {
						query: term,
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			initSelection: function(element, callback) {
				var query = $(element).val();
				var data = { id: query, text: query };
				callback(data);
			}
		});

		$(\"#assign\").select2({
			minimumInputLength: 3,
			maximumSelectionSize: 3,
			ajax: {
				url: \"xmlhttp.php?action=get_users\",
				dataType: \'json\',
				data: function (term, page) {
					return {
						query: term,
					};
				},
				results: function (data, page) {
					return {results: data};
				}
			},
			initSelection: function(element, callback) {
				var query = $(element).val();
				var data = { id: query, text: query };
				callback(data);
			}
		});
	}
// -->
</script>
</body>
</html>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_search_results",
		"template" => "{\$multipage}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=\"thead\" colspan=\"9\"><strong>{\$lang->search_results}</strong></td>
	</tr>
	<tr class=\"tcat\">
		<td>{\$lang->title_todo}</td>
		<td>{\$lang->search_project}</td>
		<td>{\$lang->date_todo}</td>
		<td>{\$lang->from_todo}</td>
		<td>{\$lang->priority_todo}</td>
		<td>{\$lang->status_todo}</td>
		<td>{\$lang->done_todo}</td>
		<td>{\$lang->assign_todo}</td>
		<td>{\$lang->version_todo}</td>
	</tr>
	{\$resulttable}
</table><br />",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);
	
	$templatearray = array(
		"title" => "todolist_search_resulttable",
		"template" => "<tr class=\"trow1\">
	<td>{\$row[\'title\']}</td>
	<td>{\$prname}</td>
	<td>{\$date}</td>
	<td>{\$from}</td>
	<td>{\$spriority}</td>
	<td>{\$sstatus}</td>
	<td>{\$done}</td>
	<td>{\$sassign}</td>
	<td>{\$row[\'version\']}</td>
</tr>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_search_resulttable_nothing",
		"template" => "<tr class=\"trow1\"><td colspan=\"9\" style=\"text-align: center;\">{\$lang->search_results_nothing}</td></tr>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_confirm",
		"template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$lang->title_overview}: {\$mybb->settings[\'todo_name\']}</title>
{\$headerinclude}
</head>
<body>
{\$header}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=\"thead\" colspan=\"2\"><strong>{\$lang->confirm_delete}</strong></td>
	</tr>
	<form action=\"todolist.php?action=delete\" method=\"post\">
	<input type=\"hidden\" name=\"id\" value=\"{\$id}\" />
	<tr class=\"trow1\">
		<td colspan=\"2\">{\$lang->confirm_delete_desc}</td>
	</tr>
	<tr class=\"trow1\">
		<td style=\"text-align: center;\"><input type=\"submit\" value=\"{\$lang->yes}\" />
			<input type=\"submit\" value=\"{\$lang->no}\" name=\"no\" /></td>
	</tr>
	</form>
</table>
{\$footer}
</body>
</html>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
		"title" => "todolist_searches",
		"template" => "<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=\"thead\" colspan=\"5\"><strong>{\$lang->searches}</strong></td>
	</tr>
	{\$searches}
</table>",
		"sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
        "title" => "todolist_new",
        "template" => "<html>
<head>
<title>{\$mybb->settings[\'bbname\']} - {\$lang->title_overview}: {\$mybb->settings[\'todo_name\']}</title>
{\$headerinclude}
</head>
<body>
{\$header}
<table border=\"0\" cellspacing=\"{\$theme[\'borderwidth\']}\" cellpadding=\"{\$theme[\'tablespace\']}\" class=\"tborder\" style=\"clear: both;\">
	<tr>
		<td class=\"thead\" colspan=\"8\"><strong>{\$lang->new}</strong></td>
	</tr>
	<tr class=\"tcat\">
		<td>{\$lang->title_todo}</td>
		<td>{\$lang->search_project}</td>
		<td>{\$lang->from_todo}</td>
		<td>{\$lang->date_todo}</td>
		<td>{\$lang->done_todo}</td>
		<td>{\$lang->status_todo}</td>
	</tr>
	{\$news}
</table>
{\$footer}
</body>
</html>",
        "sid" => -2
	);
	$db->insert_query("templates", $templatearray);

	$templatearray = array(
        "title" => "todolist_new_table",
        "template" => "	<tr class=\"trow1\">
		<td>{\$title}</td>
		<td>{\$project}</td>
		<td>{\$from}</td>
		<td>{\$date}</td>
		<td>{\$done}</td>
		<td>{\$status}</td>
	</tr>",
        "sid" => -2
    );
    $db->insert_query("templates", $templatearray);

	//Einstellung Gruppe
	$todolist_group = array(
		"title"			=> $lang->setting_group_todo,
		"name"			=> "todo",
		"description"	=> $lang->setting_group_todo_desc,
		"disporder"		=> "50",
		"isdefault"		=> "0",
	);
	$gid = $db->insert_query("settinggroups", $todolist_group);


	//Einstellungen
	$todolist_setting_1 = array(
		"name"			=> "todo_activate",
		"title"			=> $lang->setting_todo_activate,
		"description"	=> $lang->setting_todo_activate_desc,
		"optionscode"	=> "yesno",
		"value"			=> 'yes',
		"disporder"		=> '1',
		"gid"			=> (int)$gid,
	);
	$db->insert_query("settings", $todolist_setting_1);

	$todolist_setting_2 = array(
		"name"			=> "todo_name",
		"title"			=> $lang->setting_todo_name,
		"description"	=> $lang->setting_todo_name_desc,
		"optionscode"	=> "text",
		"value"			=> "",
		"disporder"		=> '2',
		"gid"			=> (int)$gid,
	);
	$db->insert_query("settings", $todolist_setting_2);

	$todolist_setting_3 = array(
		"name"			=> "todo_per_page",
		"title"			=> $lang->setting_todo_per_page,
		"description"	=> $lang->setting_todo_per_page_desc,
		"optionscode"	=> "text",
		"value"			=> "10",
		"disporder"		=> '3',
		"gid"			=> (int)$gid,
	);
	$db->insert_query("settings", $todolist_setting_3);

	$todolist_setting_4 = array(
		"name"			=> "todo_404_errors",
		"title"			=> $lang->setting_todo_404_errors,
		"description"	=> $lang->setting_todo_404_errors_desc,
		"optionscode"	=> "yesno",
		"value"			=> "no",
		"disporder"		=> '4',
		"gid"			=> (int)$gid,
	);
	$db->insert_query("settings", $todolist_setting_4);

	$todolist_setting_5 = array(
		"name"			=> "todo_pm_notify",
		"title"			=> $lang->setting_todo_pm_notify,
		"description"	=> $lang->setting_todo_pm_notify_desc,
		"optionscode"	=> "yesno",
		"value"			=> "yes",
		"disporder"		=> '5',
		"gid"			=> (int)$gid,
	);
	$db->insert_query("settings", $todolist_setting_5);
	rebuild_settings();
}

function todolist_is_installed() {
	global $db;
	return $db->table_exists("todolist");
}

function todolist_activate() {}

function todolist_deactivate() {}

function todolist_uninstall()
{
	global $db;

	$db->drop_table("todolist");
	$db->drop_table("todolist_projects");
	$db->drop_table("todolist_permissions");
	$db->drop_table("todolist_searchs");

	$query = $db->simple_select("settinggroups", "gid", "name='todo'");
	$g = $db->fetch_array($query);
	$db->delete_query("settinggroups", "gid='".$g['gid']."'");
	$db->delete_query("settings", "gid='".$g['gid']."'");
	rebuild_settings();

	//Delete templates
	$templatearray = array(
		"todolist",
		"todolist_projects",
		"todolist_show",
		"todolist_add",
		"todolist_edit",
		"todolist_table",
		"todolist_table_no_results",
		"todolist_projects_table",
		"todolist_projects_table_no_results",
		"todolist_mod",
		"todolist_mod_table",
		"todolist_edited",
		"todolist_search",
		"todolist_search_resulttable",
		"todolist_search_resulttable_nothing",
		"todolist_search_results",
		"todolist_confirm",
		"todolist_searches"
	);
	$deltemplates = implode("','", $templatearray);
	$db->delete_query("templates", "title in ('{$deltemplates}')");
}

function todo_mybbservice_actions($actions)
{
	global $page, $lang, $info;
	$lang->load("todolist");

	$actions['todo'] = array(
		"active" => "todo",
		"file" => "../config/todo.php"
	);

	$sub_menu = array();
	$sub_menu['10'] = array("id" => "todo", "title" => $lang->todo, "link" => "index.php?module=mybbservice-todo");
	$sidebar = new SidebarItem($lang->todo);
	$sidebar->add_menu_items($sub_menu, $actions[$info]['active']);

	$page->sidebar .= $sidebar->get_markup();

	return $actions;
}

function todo_admin_config_menu($sub_menu)
{
	global $lang;

	$lang->load("todolist");

	$sub_menu[] = array("id" => "todo", "title" => $lang->todo, "link" => "index.php?module=config-todo");

	return $sub_menu;
}

function todo_admin_config_action_handler($actions)
{
	$actions['todo'] = array(
		"active" => "todo",
		"file" => "todo.php"
	);

	return $actions;
}

function todo_admin_config_permissions($admin_permissions)
{
	global $lang;

	$lang->load("todolist");

	$admin_permissions['todo'] = $lang->todo_permission;

	return $admin_permissions;
}

function todo_wol_activity($user_activity)
{
	global $parameters;
	$split_loc = explode(".php", $user_activity['location']);
	if($split_loc[0] == $user['location']) {
		$filename = '';
	} else {
		$filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
	}

	switch ($filename)
	{
		case 'todolist':
			$user_activity['activity'] = "todo";
			$user_activity['todo']['action'] = $parameters['action'];

			if(isset($parameters['id']))
				$user_activity['todo']['id'] = (int)$parameters['id'];
			break;
	}

	return $user_activity;
}

function todo_wol_location($array)
{
	global $lang, $settings, $db;
	$lang->load("todolist");
	switch ($array['user_activity']['activity'])
	{
		case 'todo':
			//echo "<pre>"; var_dump($array['user_activity']['todo']); echo "</pre>";
			if(isset($array['user_activity']['todo']['id'])) {
				$id = $array['user_activity']['todo']['id'];
				$todo = $db->fetch_field($db->simple_select("todolist", "title", "id={$id}"), "title");				
				$project = $db->fetch_field($db->simple_select("todolist_projects", "title", "id={$id}"), "title");
			}

			switch ($array['user_activity']['todo']['action'])
			{
				case "show":
					$array['location_name'] = $lang->sprintf($lang->todo_wol_show, $todo, $id);
					break;
				case "show_project":
					$array['location_name'] = $lang->sprintf($lang->todo_wol_show_project, $project, $id);
					break;
				case "add":
					$array['location_name'] = $lang->todo_wol_add;
					break;
				case "delete":
					$array['location_name'] = $lang->todo_wol_delete;
					break;
				case "edit":
					$array['location_name'] = $lang->sprintf($lang->todo_wol_edit, $todo, $id);
					break;
				case "search":
					$array['location_name'] = $lang->todo_wol_search;
					break;
				default:
					$array['location_name'] = $lang->todo_wol;          	
			}
			break;
	}
	return $array;
}

function todo_no_permission()
{
	global $settings;
	if($settings['todo_404_errors'] == 1)
		header("HTTP/1.1 404 Not Found");
	else
		error_no_permission();

	exit;
}

function todo_pm($to, $subject, $message, $from=0)
{
	if(is_string($to))
		$to = explode(',', $to);
	elseif(is_int($to))
		$to = (array)$to;

	//Write PM
	require_once MYBB_ROOT."inc/datahandlers/pm.php";
	$pmhandler = new PMDataHandler();

	$pm = array(
		"subject" => $subject,
		"message" => $message,
		"icon" => "",
		"fromid" => $from,
		"do" => "",
		"pmid" => "",
		"toid" => $to
	);
	$pmhandler->set_data($pm);
	$pmhandler->admin_override = true;

	// Now let the pm handler do all the hard work.
	if($pmhandler->validate_pm())
	{
		return $pmhandler->insert_pm();
	}else {
		$pm_errors = $pmhandler->get_friendly_errors();
		$send_errors = inline_error($pm_errors);
		echo $send_errors;
		return false;
	}
}

function todo_has_any_permission($right="can_see", $user=false)
{
	global $mybb, $db;
	if(!$user)
		$user = $mybb->user;
	if(is_int($user))
		$user = get_user($user);

	if($user['additionalgroups'] != "")
		$groups = explode(",", $user['additionalgroups']);
	$groups[] = $user['usergroup'];
	
	$rights = false;
	foreach($groups as $gid) {
		$query = $db->simple_select("todolist_permissions", "pid", "gid={$gid} AND {$right}=1");
		if($db->num_rows($query) > 0)
			$rights = true;
	}
	if(!$rights) {
		$query = $db->simple_select("todolist_permissions");
		if($db->num_rows($query) == 0)
			$rights = true;
	}
	return $rights;
}

function todo_has_permission($project, $right="can_see", $user=false)
{
	global $mybb, $db;
	if(!$user)
		$user = $mybb->user;
	if(is_int($user))
		$user = get_user($user);

	if($user['additionalgroups'] != "")
		$groups = explode(",", $user['additionalgroups']);
	$groups[] = $user['usergroup'];
	
	
		

	$rights = false;
	foreach($groups as $gid) {
		$query = $db->simple_select("todolist_permissions", "pid", "pid={$project} AND gid={$gid} AND {$right}=1");
		if($db->num_rows($query) > 0)
			$rights = true;
	}
	
	
	
	return $rights;
}
?>