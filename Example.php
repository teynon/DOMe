<?php
	require "DOMe.php";
	
	$dom = new DOMe("div");
	
	$child = &$dom->newChild("span", "Hello World");
	$child->text .= "! Cool!";
	
	$header = new DOMe("h1", "Hello there!");
	
	$dom->insertBefore($child, $header);
	
	$header->text = "Welcome!";
	
	$subHeader = new DOMe("h2", "Hope you like it!");
	$dom->insertAfter($header, $subHeader);
	
	$subHeader2 = new DOMe("h2", "Blah!");
	$dom->insertLast($subHeader2);
	
	$subHeader2->text = "Humph";
	
	echo $dom->generate();
	
	$copy = $dom->copy(1);
	
	//echo "<pre>" . print_r($copy, true) . "</pre>";
	
	echo $copy->generate();
	
?>