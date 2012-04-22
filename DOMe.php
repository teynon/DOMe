<?php
####################################################
# Document Object Model - Eynon                    #
# ------------------------------------------------ #
#    This class is a custom reworking of PHP's     #
# built in document object model. The end goal     #
# was to be able to use less code to accomplish    #
# the same tasks. Plus, additional features may be #
# added to advance this class beyond the scope of  #
# PHP's DOMDocument.
#                                                  #
# ------------------------------------------------ #
# CREATED BY: Thomas Eynon                         #
#             www.thomaseynon.com                  #
# CREATED ON: 1/18/2012                            #
####################################################

if (!interface_exists("Object_Array")) {
	// Blend the interfaces.
	interface Object_Array extends Iterator, Countable {

	}
}

if (!class_exists("DOMe")) {
	class DOMe implements Object_Array {
		public $tag, $children, $attributes, $text, $allowHTML, $endTag, $id, $preValue;
		private $styles, $pointer;
		
		function __construct($tag, $value = "", $endTag = true, $allowHTML = false, $preValue = "") {
			$this->tag = $tag;
			$this->text = $value;
                        $this->preValue = $preValue;
			$this->endTag = $endTag;
			$this->allowHTML = $allowHTML;
			$this->id = 0;
			
			$this->children = array();
			$this->childIndex = array();
			$this->attributes = array();
			$this->styles = array();
			$this->pointer = 0;
		}
		
		
		###############################################################################################
		# CHILDREN - ASSIGNMENT
		###############################################################################################
		function &newChild($tag, $value = "", $endTag = true, $allowHTML = false) {
			// Build the new child.
			$child = new DOMe($tag, $value, $endTag, $allowHTML);
			
			// Get the index added of the array.
			$index = count($this->children);
			
			// Give the child the id.
			$child->id = $index;
			
			// Add to children.
			$this->children[] = $child;
			
			// Add the index information to the index array.
			$this->childIndex[] = $index;
			
			// Return the child.
			return $this->children[$index];
		}
		
		function assign(&$object) {
			
			// Get the index added of the array.
			$index = count($this->children);
			
			// Give the child the id.
			$object->id = $index;
			
			// Assign the object.
			$this->children[] = &$object;
			
			// Add the index for tracking.
			$this->childIndex[] = $index;
		}
		
		function insertBefore($reference, &$object) {
			// First assign the element.
			// Get the index added of the array.
			$index = count($this->children);
			
			// Give the child the id.
			$object->id = $index;
			
			// Assign the object.
			$this->children[] = &$object;
			
			// Check if the reference object has an id.
			if (isset($reference->id)) {
				// The reference ID is the index.
				$key = array_search($reference->id, $this->childIndex);
				
				if ($key !== false) {
					// Rebuilt the array.
					$array_left = array_slice($this->childIndex, 0, $key);
					$array_right = array_slice($this->childIndex, $key);
					
					// Add the object key, then append the right side of the array.
					array_push($array_left, $index);
					
					// Merge the array back together.
					$this->childIndex = array_merge($array_left, $array_right);
				}
			}
		}
		
		function insertAfter($reference, &$object) {
			// First assign the element.
			// Get the index added of the array.
			$index = count($this->children);
			
			// Give the child the id.
			$object->id = $index;
			
			// Assign the object.
			$this->children[] = &$object;
			
			// Check if the reference object has an id.
			if (isset($reference->id)) {
				// The reference ID is the index.
				$key = array_search($reference->id, $this->childIndex);
				
				if ($key !== false) {
					// Rebuilt the array.
					$array_left = array_slice($this->childIndex, 0, $key + 1);
					$array_right = array_slice($this->childIndex, $key + 1);
					
					// Add the object key, then append the right side of the array.
					array_push($array_left, $index);
					
					// Merge the array back together.
					$this->childIndex = array_merge($array_left, $array_right);
				}
			}
		}
		
		function insertFirst(&$object) {
			// First assign the element.
			// Get the index added of the array.
			$index = count($this->children);
			
			// Give the child the id.
			$object->id = $index;
			
			// Assign the object.
			$this->children[] = &$object;
			
			// Check if the reference object has an id.
			array_unshift($this->childIndex, $index);
		}
		
		function insertLast(&$object) {
			$this->assign($object);
		}
		
		// $levels indicates how many levels down you want to clone. -1 means all.
		function copy($levels = -1) {
			$object = new DOMe($this->tag, $this->text, $this->endTag, $this->allowHTML);
			
			$object->attributes = $this->attributes;
			$object->styles = $this->styles;
			
			if ($levels != 0) {
				// Copy the children.
				if ($levels > 0) --$levels;
				
				$object->childIndex = $this->childIndex;
				
				foreach ($object->childIndex as $key) {
					$object->children[$key] = $this->children[$key]->copy($levels);
				}
			}
			
			return $object;
		}
		
		function remove($reference) {
			
			// Check if the reference object has an id.
			if (isset($reference->id)) {
				// The reference ID is the index.
				$key = array_search($reference->id, $this->childIndex);
					
				if ($key !== false) {
					// Rebuilt the array.
					$array_left = array_slice($this->childIndex, 0, $key);
					$array_right = array_slice($this->childIndex, $key + 1);
			
					// Merge the array back together.
					$this->childIndex = array_merge($array_left, $array_right);
					
					// Unset the value.
					unset($this->children[$reference->id]);
				}
			}
		}
		
		###############################################################################################
		# CHILDREN - RETRIEVAL
		###############################################################################################
		
		// Retrieve the first element to match the tag.
		function &getElementByTagName($tag, $levels = -1) {
			$result = false;
			if ($this->tag == $tag) {
				return $this;
			}
			
			// Search in order
			foreach ($this->childIndex as $key) {
				$result = &$this->children[$key]->getElementByTagName($tag);
				if ($result !== false) {
					return $result;
				}
			}
			
			return $result;
		}
		
		// Get Nth child.
		function &child($key) {
			if (isset($this->childIndex[$key]))
				return $this->children[$this->childIndex[$key]];
			
			return false;
		}
		
		
		###############################################################################################
		# ATTRIBUTES
		###############################################################################################
		
		function setAttribute($attributeName, $value = "", $caseSensitive = false) {
			// If its case sensitive, don't set it to lower case. Unless it's the style tag. Then it should always be lower case, so we can access it easily within the class.
			if (!$caseSensitive || strtolower($attributeName) == "style") {
				$attributeName = strtolower($attributeName);
			}
			
			$this->attributes[$attributeName] = htmlspecialchars($value, ENT_COMPAT);
			
			// If we set a style, get the current styles.
			if (strtolower($attributeName) == "style") $this->buildStyles();
			
			return true;
		}
		
		function setAttributes($string, $caseSensitive = false) {
			// Parse the string. Regex doesn't seem to work well here if javascript is included in attributes.
			$attribute = "";
			$value = "";
			$attributes = array();
			$flag = 0;
			$closeChar = null;
			$ignoreNextChar = false;
			
			// Loop through each character in the string.
			for ($i = 0; $i < strlen($string); $i++) {
				
				// When flag is zero, we are looking for an attribute.
				if ($flag == 0) {
					
					// Attributes can't have spaces between the equal.
					if (!ctype_space($string[$i])) {
						
						// If we find an = sign, we are moving from the attribute name to the value.
						if ($string[$i] != "=") {
							
							// Append character to attribute.
							$attribute .= $string[$i];
						}
						else {
							// Assign the attribute with an empty value, then move on to Encapsulation step.
							$attributes[$attribute] = "";
							$flag = 1;
						}
					}
					else {
						// In case someone is trying to pass an attribute like "checked"
						if (!empty($attribute))
							$attributes[$attribute] = "";
						$attribute = "";
					}
				}
				else if ($flag == 1) {
					// Encapsulation
					// We need to determine the opening character so we know what the end character is.
					if ($string[$i] == "'")
						$closeChar = "'"; // Open character is a single quote, so end character is a single quote.
					else if ($string[$i] == '"')
						$closeChar = '"'; // Open character is a double quote, so end character is a double quote.
					else if (ctype_space($string[$i])) {
						// If it's a space, this value lacks proper encapsulation, next space we find will close the value. Append character to value string.
						$closeChar = null;
						$value = $string[$i]; 
					}
					
					// Move on to value step.
					$flag = 2;
				}
				else if ($flag == 2) {
					// If a space closes the value and a space is found, close the value.
					if ($closeChar == null && ctype_space($string[$i])) {
					echo $value ." |1 ";
						$attributes[$attribute] = $value;
						$attribute = "";
						$value = "";
						$flag = 0;
						$closeChar = null;
						continue;
					}
					else if ($closeChar == $string[$i] && !$ignoreNextChar) {
						// If the close character was found and it's not escaped, end the value.
						$attributes[$attribute] = $value;
						$attribute = "";
						$value = "";
						$flag = 0;
						$closeChar = null;
						continue;
					}
					
					// If the previous char was an escape character, set the flag back to false.
					if ($ignoreNextChar) $ignoreNextChar = false;
					
					// Append the character to the value.
					$value .= $string[$i];
					
					// If this is an escape character, set the escape flag to true.
					if ($string[$i] == "\\") {
						$ignoreNextChar = true;
					}
				}
			}
			if (!empty($attribute)) $attributes[$attribute] = $value;
			
			// Now set each individual attribute
			foreach ($attributes as $key => $value) {
				$this->setAttribute($key, $value, $caseSensitive);
			}
		}
	
		// Recursively assign attributes to specified tags.
		// --------------------------------------------------
		// USAGE:
		//		$attributeName - The attribute we are adding
		//			or changing.
		//		$newName - What we are changing the attribute to.
		//		$newValue - What we are setting the value to.
		//					0 means leave the value as it is.
		//		$tagFilter - An array with tag names.
		//					If the array is empty, then it
		//					applies to everything.
		//					If it has a tag name, it
		//					will only apply values to elements
		//					that have that tag set.
		//		$attributeFilter - An array with attributes and values.
		//					If the attribute is found and has the value
		//					described, the action will apply.
		//
		// EXAMPLE:
		//		setAttribute_r("disabled", "disabled", "true", array("input"));
		//			- This will recursively search and set the attribute ("disabled")
		//			to any element that has an attribute of "input".
		//			IE: All input boxes will be disabled.
		/////////////////////////////////////////////////////
		function setAttribute_r($attributeName, $newName, $newValue = 0, $tagFilter = array(), $attributeFilter = array(), $matchAll = false) {
			$replace = false;
	
			if (count($tagFilter) > 0) { // We are applying it specifically.
	
				// Loop through the filter array and match values
				if (in_array($this->tag, $tagFilter)) {
	
					// See if there are attribute filters.
					if (count($attributeFilter) > 0) {
						// If we require that all attributes exist, set replace to true and if we find that one doesn't exist, we will set it to false.
						if ($matchAll) {
							$replace = true;
						}
	
						// Make sure that the attribute exists.
						foreach ($attributeFilter as $attr => $val) {
							if (isset($this->attributes[$attr]) && $this->attributes[$attr] == $val) {
								// An attribute has matched
								if (!$matchAll) {
									$replace = true;
									break;
								}
							}
							else {
								// If matchall is on and we have one that doesn't match, then we don't process the replacement.
								$replace = false;
							}
						}
					}
					else {
						$replace = true;
					}
				}
			}
			else { // We are applying this no matter what the tag name is.
	
				// See if there are attribute filters.
				if (count($attributeFilter) > 0) {
					// If we require that all attributes exist, set replace to true and if we find that one doesn't exist, we will set it to false.
					if ($matchAll) {
						$replace = true;
					}
	
					// Make sure that the attribute exists.
					foreach ($attributeFilter as $attr => $val) {
	
						if (isset($this->attributes[$attr]) && $this->attributes[$attr] == $val) {
							// An attribute has matched
							if (!$matchAll) {
								$replace = true;
								break;
							}
						}
						else {
							// If matchall is on and we have one that doesn't match, then we don't process the replacement.
							$replace = false;
						}
					}
				}
				else {
					$replace = true;
				}
			}
	
			if ($replace) {
				$this->swapAttribute($attributeName, $newName);
	
				// If there is a new value, assign it.
				if ($newValue !== 0) {
					$this->setAttribute($newName, $newValue);
				}
			}
	
			// Run this on all the child elements.
			foreach ($this->childIndex as $key) {
				$this->children[$key]->setAttribute_r($attributeName, $newName, $newValue, $tagFilter, $attributeFilter, $matchAll);
			}
		}
	
		// Runs a function on all values in an object tree.
		function valueWalk($function) {
	
                    // Run the function.
                    $this->value = $function($this->value);

                    // Run this on all the child elements.
                    foreach ($this->elements as $key => $element) {
                        $this->elements[$key]->valueWalk($function);
                    }
		}
	
		function swapAttribute($attributeName, $newName) {
                    $value = "";

                    if (isset($this->attributes[$attributeName])) {
                        // Create temporary storage
                        $value = $this->attributes[$attributeName];

                        // Remove this attribute.
                        unset($this->attributes[$attributeName]);
                    }

                    // Create the new one.
                    $this->attributes[$newName] = $value;
		}
	
		###############################################################################################
		# STYLES
		###############################################################################################
		
		// Get a specified style's value.
		function getStyle($style) {
                    if ($this->styleExists($style))
                        return $this->styles[$style];

                    return null;
		}
		
		// Check if a style exists.
		function styleExists($style) {
                    // Always build the style again, because a style can be set through the attribute tag or manually.
                    //$this->buildStyles();

                    if (isset($this->styles[$styleName]))
                        return true;

                    return false;
		}
		
		// Set the specified style. If it does not exist, create it.
		function setStyle($style, $value, $caseSensitive = false) {
                    //$this->buildStyles();

                    if (!$caseSensitive)
                        $style = strtolower($style);

                    $this->styles[$style] = $value;

                    $this->setAttribute("style", $this->generateStyle());
		}
		
		function removeStyle($style, $caseSensitive = false) {
                    //$this->buildStyles();

                    if (!$caseSensitive)
                        $style = strtolower($style);

                    if (isset($this->styles[$style]))
                        unset($this->styles[$style]);

                    $this->setAttribute("style", $this->generateStyle());
		}
		
		// Generate the style string.
		function generateStyle() {
                    $styleString = "";

                    // Build the new styles tag.
                    foreach ($this->styles as $style => $value) {
                            $styleString .= "{$style}: {$value};";
                    }

                    return $styleString;
		}
		
		// Build the current styles.
		function buildStyles() {
                    // Set the styles array to empty.
                    $this->styles = array();

                    // See if the style attribute exists.
                    if (isset($this->attributes['style'])) {

                        // Explode the styles into individuals
                        $style_attr = explode(";", $this->attributes['style']);

                        // Build an array of the styles.
                        foreach ($style_attr as $value) {
                            $style_info = explode(":", $value);

                            if (!empty($style_info[0])) {
                                // trim and lower the style name tags.
                                $style_info[0] = strtolower(trim($style_info[0]));
                                $style_info[1] = trim($style_info[1]);

                                $this->styles[$style_info[0]] = $style_info[1];
                            }
                        }
                    }
		}
                
		###############################################################################################
		# Importing
		###############################################################################################
                function importHTML($html, $root = true) {
                    // Strip comments.
                    if ($root) $html = preg_replace("@<!--(.*?)-->@", "", $html);

                    // These tags dont have end tags and should not be incremented.
                    $selfClosing = array("img", "br", "input", "hr");

                    // First determine if there is a tag.
                    while (strlen($html) > 0) {
                        // Trim whitespace
                        $html = trim($html);
                        
                        // If there is a tag.
                        if (substr_count($html, "<") > 0) {
                            // Go to the first < tag.
                            $text = substr($html, 0, strpos($html, "<"));
                            
                            // If there is text, append it to the current elements value.
                            if (strlen($text) > 0) $this->text = $text;

                            // Clear everything before the tag.
                            $html = substr($html, strlen($text) + 1);

                            // If the next character is a slash, we are ending this element.
                            if (substr($html, 0, 1) == "/") {
                                $html = substr($html, strpos($html, ">") + 1);
                                break;
                            }
                            else {

                                // Get the tag name.
                                $tag = substr($html, 0, strpos($html, ">"));
                                $tagName = substr($tag, 0, strpos($tag, " "));
                                if (empty($tagName)) $tagName = $tag;
                                $endTag = true;
                                if (in_array($tagName, $selfClosing)) {
                                        $endTag = false;
                                }
                                $element = &$this->newChild($tagName, "", $endTag);
                                $html = substr($html, strlen($tagName));

                                // Get any attributes
                                $attributes = substr($html, 0, strpos($html, ">"));

                                $element->setAttributes($attributes, false);
                                $html = substr($html, strpos($html, ">") + 1);
                                if ($endTag) {
                                        // Run through the data again on the child.
                                        $html = $element->importHTML($html, false, ++$count);
                                }
                            }
                        }
                        else {
                            if (strlen($html > 0)) {
                                $this->value = $html;
                                substr($html, strlen($this->value));
                            }
                        }
                    }
			
                    if ($root) {
                        return $this;
                    }
                    else {
                        return $html;
                    }
		}
		
		###############################################################################################
		# OUTPUT
		###############################################################################################
		function generate($tidy = true, $indent = 0, $indentStr = "  ") {
                    $indentText = "";
                    $tidyText = "";
                    $indentStr = "";

                    if ($tidy) {
                        // How far in should we indent this?
                        for ($x = 0; $x < $indent; $x++) {
                            // Add the indent character to the indent string.
                            $indentText .= $indentStr;
                        }

                        $tidyText = "\n";
                    }
                    else {
                        $indentText = "";
                        $tidyText = "";
                        $indentStr = "";
                    }

                    $data = "";

                    if (!empty($this->preValue)) $data = $indentText . $this->preValue . $tidyText;

                    $data .= "{$indentText}<{$this->tag}";

                    foreach ($this->attributes as $key=>$value) {
                        $data .= " {$key}=\"{$value}\"";
                    }
                    if ($this->endTag == false) {
                        $data .=" /";
                    }
                    $data .= ">";

                    if ($tidy) {
                        $data .= $tidyText;
                    }

                    $this->text = preg_replace("@([\n]+)@i", "{$indentText}{$indentStr}$1", $this->text);
                    if ($this->allowHTML) {
                        if (!empty($this->text))
                            $data .= $indentText.$indentStr.$this->text;
                    }
                    else {
                        if (!empty($this->text))
                            $data .= $indentText.$indentStr.htmlspecialchars($this->text, ENT_QUOTES);
                    }
                    if (!empty($this->text))
                            $data .= $tidyText;

                    // Loop through the children.
                    foreach ($this->childIndex as $index) {
                        if (is_object($this->children[$index]) && get_class($this->children[$index]) == "DOMe") {
                            $data .= $this->children[$index]->generate($tidy, $indent + 1, $indentStr);
                        }
                    }

                    // Draw the end tag?
                    if ($this->endTag) {
                        $data .= "{$indentText}</{$this->tag}>";
                        if ($tidy) {
                            $data .= $tidyText;
                        }
                    }
                    return $data;
		}
		
		// Iterator template methods
		function current() {
                    return $this->children[$this->childIndex[$this->pointer]];
		}
		
		function next() {
                    $this->pointer++;
                    if (isset($this->childIndex[$this->pointer])) {
                        return $this->children[$this->childIndex[$this->pointer]];
                    }

                    return false;
		}
		
		function valid() {
                    if (isset($this->childIndex[$this->pointer])) {
                            return true;
                    }
                    return false;
		}
		
		function rewind() {
                    $this->pointer = 0;
		}
		
		function key() {
                    return $this->pointer;
		}
		
		function count() {
                    return count($this->childIndex);
		}
	}
}