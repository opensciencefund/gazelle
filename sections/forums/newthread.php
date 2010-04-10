<?
/*
New post page

This is the page that's loaded if someone wants to make a new topic.

Information to be expected in $_GET:
	forumid: The ID of the forum that it's being posted in

*/

$ForumID = $_GET['forumid'];
if(!is_number($ForumID)) {
	error(404);
}
$Forum = get_forum_info($ForumID);
if($Forum === false) {
	error(404);
}


if($LoggedUser['Class'] < $Forum['MinClassCreate']) { error(403); }
show_header('Forums > '.$Forum['Name'].' > New Topic');
?>
<div class="thin">
	<h2><a href="forums.php">Forums</a> &gt; <a href="forums.php?action=viewforum&amp;forumid=<?=$ForumID?>"><?=$Forum['Name']?></a> &gt; New Topic</h2>
	<div class="box pad">
		<form action="" method="post">
			<input type="hidden" name="action" value="new" />
			<input type="hidden" name="forum" value="<?=$ForumID?>" />
			<table>
				<tr>
					<td class="label">Title</td>
					<td><input id="title" type="text" name="title" style="width: 98%;" /></td>
				</tr>
				<tr>
					<td class="label">Body</td>
					<td><textarea id="posttext" style="width: 98%;" onkeyup="resize('posttext');" name="body" cols="90" rows="8"></textarea></td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="checkbox" name="subscribe" id="subscribebox" checked="checked" />
						<label for="subscribebox">Subscribe to topic</label>
					</td>
<? if (check_perms('forums_polls_create')) { ?>
				<script type="text/javascript">
				var AnswerCount = 1;

				function AddAnswerField() {
						if (AnswerCount >= 25) { return; }
						var AnswerField = document.createElement("input");
						AnswerField.type = "text";
						AnswerField.id = "answer";
						AnswerField.name = "answers[]";
						AnswerField.style.width = "90%";
		
						var x = $('#answer_block').raw();
						x.appendChild(document.createElement("br"));
						x.appendChild(AnswerField);
						AnswerCount++;
				}

				function RemoveAnswerField() {
						if (AnswerCount == 1) { return; }
						var x = $('#answer_block').raw();
						for (i=0; i<2; i++) { x.removeChild(x.lastChild); }
						AnswerCount--;
				}
				</script>
				<tr>
					<td colspan="2" class="center">
						<strong>Poll Settings</strong> 
						<a href="#" onclick="$('#poll_question, #poll_answers').toggle();return false;">(View)</a> 
					</td>
				</tr>
				<tr id="poll_question" class="hidden">
					<td class="label">Question</td>
					<td><input type="text" name="question" style="width: 98%;" /></td>
				</tr>
				<tr id="poll_answers" class="hidden">
					<td class="label">Answers</td>
					<td id="answer_block">
						<input type="text" name="answers[]" style="width: 90%;" />
						[<a href="#" onclick="AddAnswerField();return false;">+</a>]
						[<a href="#" onclick="RemoveAnswerField();return false;">-</a>]
					</td>
				</tr>
<? } ?>
			</table>
			<input type="submit" value="Create thread" />
		</form>
	</div>
</div>
<? show_footer(); ?>
