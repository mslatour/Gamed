<?php
session_name("s");
session_start();

// Generate new bit string of $dim dimensions 
// by making $numflip mutations to $bits
function generate($bits, $numflip, $dim){
  $mask = 0x0;
  for($i = 0; $i < $numflip; $i++){
    $r = rand(0,$dim-1);
    $mask = $mask | (0x1<<$r);
  }
  return $bits ^ $mask;
}

// Feature representations
$features = array();
$features[] = array(
  "This is an individual assignment",
  "On this assignment you work in groups",
  "On this type of assignment you are asked to work alone",
  "On this type of assignment you are asked to work in a group and cooperate on the final product"
);
$features[] = array(
  "Each individual sub-assignment will be graded",
  "The grade for the assignment is based on the final product",
  "This is an assignment that is split up into several smaller assignments or exercises. The smaller assignments are not necessarily handed in separately. For instance, an assignment consisting of multiple exercises for which each exercise is graded",
  "This is a large assignment for which the grade is based on the complete assignment"
);
$features[] = array(
  "Your work will be graded by your fellow students",
  "Your work is graded by the TA",
  "This assignment is peer-reviewed which means that your grade is given to you by a fellow student. Naturally this happens under the moderation of the teaching assistant",
  "The grade for this assignment is given by the teaching assistant"
);
$features[] = array(
  "",
  "The assignments will be graded relative to other students",
  "",
  "Your grade is based on the performance of other students. For instance, when all students have presented the grades are based on how well other students presented. Another possibility is that the practical is in the form of a competition for which the highest score results in the highest grade"
);
$features[] = array(
  "This assignment is for your own personal benefit and thus the grade for ".
    "this assignment will not be considered for the final grade of the course",
  "",
  "The assignment is not considered in the final computation of your grade for the course. The assignment is still mandatory and you might receive a grade, but the grade is only meant as feedback",
  ""
);
$features[] = array(
  "",
  "During the assignment, the TA can give special badges that you can earn ".
    "for being most creative or most innovative or for best performance",
  "",
  "By achieving certain goals or performing certain tasks you can receive badges. These badges represent things like being the first to hand in the assignment, or getting the best result, being most innovative or creative, being the last to hand it in, doing extra work"
);
$features[] = array(
  "",
  "Students that perform well can attend a masterclass on the subject",
  "",
  "By performing well on the assignment, you will get access to some extra content. In this case, the extra content is in the form of a master class given by a famous professor which you can attend"
);
$features[] = array(
  "The deadline is only meant as a guideline and will not be enforced",
  "There is a strict deadline for this assignment",
  "On this assignment there is not a strict deadline. Naturally, there is a date to hand in your work, but this date is meant as a guideline and it will not be enforced",
  "On this assignment there is a strict deadline. Failing to hand in your work before this deadline also means you fail the assignment and your grade will be modified accordingly"
);
$features[] = array(
  "You will hand in multiple sub-assignments",
  "All work will be handed in at the end of the assignment",
  "The assignment has multiple moments in which the work so far has to be handed in. This does not mean that each separate assignment is graded",
  "The assignment is handed in as one complete work. However, the assignment may consist of multiple parts that are graded separately"
);

// Constants
$n = 20;                  // Number of configurations
$dim = sizeof($features); // Number of dimensions in each configuration
$numflip = $dim-2;  // Number of mutations in each configuration

if(isset($_REQUEST['motivation']) && $_REQUEST['motivation'] != ""){
  if(!isset($_SESSION['answers']) || sizeof($_SESSION['answers'])==0){
    $_SESSION['answers'] = array();
  }
  $config = $_REQUEST['config'];
  $configstr = "";
  for($i = 0; $i<$dim; $i++){
    $configstr .= ($configstr==""?"":",").(($config>>($dim-1-$i))&0x1);
  }
  array_shift($_SESSION['configs']);
  $to = "gameddata@gmail.com";
  $from = "latour@uva.nl";
  $subject = session_id();
  $body = sprintf("%s,%s,%s",$_SESSION['meta'],$configstr,$_REQUEST['motivation']);
  mail($to, $subject, $body, "From: $from");
}


$meta_error = "";
if(isset($_REQUEST['program']) && $_REQUEST['program'] != ""){
  if(
    $_REQUEST['program'] == "none" ||
    $_REQUEST['level'] == "none" ||
    $_REQUEST['gender'] == "none" ||
    $_REQUEST['generalmot'] == "none"
  ){
    $meta_error = "<span style='color: red'>Please fill in all questions.</span><br />";
  }else{
    $_SESSION['meta'] = $_REQUEST['program'].",".$_REQUEST['level'].",".$_REQUEST['gender'].",".$_REQUEST['generalmot'];
  }
}

if(isset($_REQUEST['f']) && $_REQUEST['f']==0){
  $_SESSION['configs'] = array();

  // Generate $n configurations
  $bits = 0x0;
  for($i = 0; $i < $n; $i++){
    $bits = generate($bits, $numflip, $dim);
    $_SESSION['configs'][] = $bits;
  }
  unset($_REQUEST['f']);
  header("Location: ./");
}

// If the meta questions have not been stored yet
if( !isset($_SESSION['meta']) || $_SESSION['meta'] == ""){

  // If the list of feature configurations is empty, generate it
  if(
    !isset($_SESSION['configs']) || 
    sizeof($_SESSION['configs']) == 0
  ){
    $_SESSION['configs'] = array();
    
    // Generate $n configurations
    $bits = 0x0;
    for($i = 0; $i < $n; $i++){
      $bits = generate($bits, $numflip, $dim);
      $_SESSION['configs'][] = $bits;
    }
  }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head><title>Survey</title></head>
<body>
<?php echo $meta_error;?>
<form method="POST">
<input type='hidden' name='<?php echo session_name();?>' value='<?php echo session_id(); ?>' />
Educational program:
<select name='program'>
  <option value='none'>Select what fits best</option>
  <option value='0'>Artificial Intelligence</option>
  <option value='1'>Computer Science</option>
  <option value='2'>Information Sciences (Informatiekunde)</option>
  <option value='3'>Beta-gamma</option>
  <option value='4'>Other</option>
</select><br />
Level:
<select name='level'>
  <option value='none'>Select what fits best</option>
  <option value='0'>First year Bachelor</option>
  <option value='1'>Second year Bachelor</option>
  <option value='2'>Third year Bachelor</option>
  <option value='3'>First year Master</option>
  <option value='4'>Second year Master</option>
</select><br />
Gender:
<select name='gender'>
<option value='none'>Select your gender</option>
<option value='0'>Male</option>
<option value='1'>Female</option>
</select><br />
Motivation in general:
<select name='generalmot'>
<option value='none'>Select what best describes your motivation for assignments in general</option>
<option value='0'>I'm highly demotivated for assignments</option>
<option value='1'>I'm demotivated for assignments</option>
<option value='2'>I'm neutral when it comes to assignments</option>
<option value='3'>I'm motivated for assignments</option>
<option value='4'>I'm highly motivated for assignments</option>
</select>
<br />
<input type='submit' value='Continue' />
</form>
</body>
</html>
<?php
// If there are still configurations to ask
} else if(sizeof($_SESSION['configs'])>0){
  // Pick the first unused configuration
  $config = $_SESSION['configs'][0];

  //DEBUG: printf("%0".$dim."b - %d left<br />",$config,sizeof($_SESSION['configs']));

  $strings = array();
  $explanations = array();
  // Verbalize the configuration
  for($i = 0; $i < $dim; $i++){
    $str = $features[$i][($config >> $i) & 0x1];
    $exp = $features[$i][(($config >> $i) & 0x1)+2];
    if($str!=""){
      $strings[] = $str;
      $explanations[] = $exp;
    }
  }

  $keys = array_rand($strings, sizeof($strings));
  $tmp1 = array();
  $tmp2 = array();
  foreach($keys as $key){
    $tmp1[] = $strings[$key];
    $tmp2[] = $explanations[$key];
  }
  $strings = $tmp1;
  $explanations = $tmp2;
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head><title>Survey</title></head>
<body>
<style>
.small {
  font-size: small;
}
.bold {
  font-weight: bold;
}
.title {
  font-size: large;
}
</style>
<span class='small bold'>
<?php
if(sizeof($_SESSION['configs'])==1){
  echo "This is the last question.";
}else{
  echo "Just ".sizeof($_SESSION['configs'])." more questions to go.";
}
?>
</span>
<div class='title'>
The following attributes are mentioned in the assignment description, describe your initial motivation.
</div>
<ol>
<?php
for($i = 0; $i < sizeof($strings); $i++){
  echo "<li>".$strings[$i]."</li>";
}
?>
</ol>
<div class='title'>Initial motivation:</div>
<form method="POST">
<input type='hidden' name='<?php echo session_name();?>' value='<?php echo session_id(); ?>' />
<input type='hidden' name='config' value='<?php echo $config; ?>' />
<input name='motivation' type='radio' value='-2' /> Highly demotivated <br />
<input name='motivation' type='radio' value='-1' /> Demotivated <br />
<input name='motivation' type='radio' value='0' /> Neutral <br />
<input name='motivation' type='radio' value='1' /> Motivated <br />
<input name='motivation' type='radio' value='2' /> Highly motivated <br />
<br />
<input type='submit' value='Next' />
</form>
<br />
<hr>
<div class='title'>Explanations:</div>
<ol class='small'>
<?php
for($i = 0; $i < sizeof($explanations); $i++){
  echo "<li>".$explanations[$i]."</li>";
}
?>
</ol>
</body>
</html>
<?php 
// If all configurations have been asked, close and thank.
}else{
  if(isset($_REQUEST['f']) && $_REQUEST['f']==1){
    $_SESSION = array();
    session_destroy();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head><title>Survey</title></head>
<body>
<h2>Thank you for your participation!</h2>You can now close this page.
</body>
</html>
<?php
  }else{
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head><title>Survey</title></head>
<body>
<h2>This was very helpful!</h2>
<a href='./?f=1'>I'm done, get me out of here!</a><br />
<a href='./?f=0'>I could do some more, if that makes you happy!</a>
</body>
</html>
<?php
  }
}
?>
