<?php
/*
Provides function(s) for reading text files containing questions and answers.
Currently supports:
- multiple choice questions
- numeric input questions
See documentation for a full explination of question file format.
*/

//reads in questions from a file and dumps it to the correct smarty variables
function get_questions($smarty, $question_file){
	$question_text = array(); //the actual question
	$questions = array(); //the options for multiple choice questions. precision for numeric questions 
	$answers = array(); //an index to the correct choice, or a number representing the correct answer
	$type = array(); //"numerical" or "multiselect"
	$image_link = array(); //html to show next to the question (mainly for images + caption)

	//read the questions
	$fin = fopen($question_file, "r") or exit("Couldn't open file.");
	while(!feof($fin)){
		$fline = fgets($fin);
		if(feof($fin))break;
		if($fline[0] == "#"){ //start a new multiple choice question
			$questions_text[] = substr($fline, 1);
			$questions[] = array();
			$type[] = "multiselect";
			$correct_text[] = "";
			$answers[] = 0;
			$image_link[]="";
		}else if($fline[0] == "%"){ //start a new numeric question
			$questions_text[] = substr($fline, 1);
			$questions[] = array();
			$type[] = "numerical";
			$correct_text[] = "";
			$answers[] = 0;
			$image_link[]="";
		}else if($type[count($type)-1] == "numerical" && $fline[0] == "*"){ //read a numeric answer
			if($fline[strlen($fline)-1] == "\n")
				$fline = substr($fline, 0, -1);
			$answers[count($answers)-1] = substr($fline, 1);
		}else if($type[count($type)-1] == "numerical"){ //read the number of digits of precision required for a numeric question
			if($fline[strlen($fline)-1] == "\n")
				$fline = substr($fline, 0, -1);
			$questions[count($questions)-1][] = $fline;
		}else if($fline[0] == "@"){ //read the text that gets shown when the user gets the question correct
			if($fline[strlen($fline)-1] == "\n")
				$fline = substr($fline, 0, -1);
			$correct_text[count($correct_text)-1] = substr($fline,1);
		}else if($fline[0] == "&"){ //read the text/html which gets show in a box next to the question (for images mainly)
			if($fline[strlen($fline)-1] == "\n")
				$fline = substr($fline, 0, -1);
			$image_link[count($image_link)-1] = substr($fline,1);
		}else{
			if($fline[0] == "*"){ //read a multiple choice answer
				$answers[count($answers)-1] = count(end($questions));
				$fline = substr($fline, 1);
			}
			$questions[count($questions)-1][] = $fline; //read a multiple choice option
		}
	}
	fclose($fin);

	//randomize the order of options for multiple choice questions
	for($i=0;$i<count($questions);$i+=1){
		if($type[$i] != "multiselect")
			continue;
		$nums = range(0, count($questions[$i])-1);
		shuffle($nums);
		$answers[$i] = $nums[$answers[$i]];
		$newq = array();
		$newq = range(0, count($questions[$i])-1);
		$j = 0;
		foreach($nums as $num){
			$newq[$num] = $questions[$i][$j];
			$j+=1;
		}
		$questions[$i] = $newq;
	}
	
	$smarty->assign("questions_text", $questions_text);
	$smarty->assign("questions", $questions);
	$smarty->assign("answers", $answers);
	$smarty->assign("types", $type);
	$smarty->assign("correct_text", $correct_text);
	$smarty->assign("image_link", $image_link);
}
?>
