<?php
/*
* filename: myexception.class.php
* author: wbq
* create date: April 9th, 2009
* last modified: April 9th, 2009
* descript: print the formated exception
*/
class MyException extends Exception
{
	public function __toString()
	{
		return "<table border=0>
				 <tr>
				  <td>Exception ".$this->getCode().": ".$this->getMessage()."</td>
				 </tr>
				 <tr>
				  <td>in ".$this->getFile()." on line ".$this->getLine()."</td>
				 </tr>
				</table>
			   ";
	}
}