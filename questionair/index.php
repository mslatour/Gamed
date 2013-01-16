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
  "On this assignment you work in groups"
);
$features[] = array(
  "Each individual sub-assignment will be graded",
  "The grade for the assignment is based on the final product"
);
$features[] = array(
  "Your work will be graded by your fellow students",
  "Your work is graded by the TA"
);
$features[] = array(
  "",
  "The assignments will be graded relative to other students"
);
$features[] = array(
  "This assignment is for your own personal benefit and thus the grade for ".
    "this assignment will not be considered for the final grade of the course",
  ""
);
$features[] = array(
  "",
  "During the assignment, the TA can give special badges that you can earn ".
    "for being most creative or most innovative or for best performance"
  );
$features[] = array(
  "",
  "Students that perform well can attend a masterclass on the subject"
);
$features[] = array(
  "The deadline is only meant as a guideline and will not be enforced",
  "There is a strict deadline for this assignment"
);
$features[] = array(
  "You will hand in multiple sub-assignments",
  "All work will be handed in at the end of the assignment"
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
  $_SESSION['answers'][] = sprintf("%s,%s,%s",$_SESSION['meta'],$configstr,$_REQUEST['motivation']);
}

// If the list of feature configurations is empty, generate it
if(
  (
    !isset($_SESSION['answers']) || 
    sizeof($_SESSION['answers']) == 0
  ) &&
  (
    !isset($_SESSION['configs']) || 
    sizeof($_SESSION['configs']) == 0
  )
){
  $_SESSION['configs'] = array($n);
  
  // Generate $n configurations
  $bits = 0x0;
  for($i = 0; $i < $n; $i++){
    $bits = generate($bits, $numflip, $dim);
    $_SESSION['configs'][] = $bits;
  }
}

$meta_error = "";
if(isset($_REQUEST['program']) && $_REQUEST['program'] != ""){
  if(
    $_REQUEST['program'] == "none" ||
    $_REQUEST['gender'] == "none" ||
    $_REQUEST['generalmot'] == "none"
  ){
    $meta_error = "<span style='color: red'>Please fill in all questions.</span><br />";
  }else{
    $_SESSION['meta'] = $_REQUEST['program'].",".$_REQUEST['gender'].",".$_REQUEST['generalmot'];
  }
}

// If the meta questions have not been stored yet
if( !isset($_SESSION['meta']) || $_SESSION['meta'] == ""){
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
  <option value='0'>First year BSc Kunstmatige Intelligentie</option>
  <option value='1'>Second year BSc Kunstmatige Intelligentie</option>
  <option value='2'>Third year BSc Kunstmatige Intelligentie</option>
  <option value='3'>First year BSc Informatica</option>
  <option value='4'>Second year BSc Informatica</option>
  <option value='5'>Third year BSc Informatica</option>
  <option value='6'>BSc Beta-gamma</option>
  <option value='7'>MSc Artificial Intelligence</option>
  <option value='8'>Other</option>
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
} else if(sizeof($_SESSION['configs'])>1){
  // Pick the first unused configuration
  $config = array_shift($_SESSION['configs']);

  //DEBUG: printf("%0".$dim."b - %d left<br />",$config,sizeof($_SESSION['configs']));

  $strings = array();
  // Verbalize the configuration
  for($i = 0; $i < $dim; $i++){
    $str = $features[$i][($config >> $i) & 0x1];
    if($str!=""){
      $strings[] = $str;
    }
  }

  shuffle($strings);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head><title>Survey</title></head>
<body>
<?php
if(sizeof($_SESSION['configs'])==1){
  echo "This is the last question.";
}else{
  echo "Just ".sizeof($_SESSION['configs'])." more questions to go.";
}
?>
<ul>
<?php
for($i = 0; $i < sizeof($strings); $i++){
  echo "<li>".$strings[$i]."</li>";
}
?>
</ul>
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
</body>
</html>
<?php 
// If all configurations have been asked, close and thank.
}else{

$to = "gameddata@gmail.com";
$from = "latour@uva.nl";
$subject = "[gameddata-".time()."]";
$body = implode("\n",$_SESSION['answers']);
mail($to, $subject, $body, "From: $from");
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
}
?>
