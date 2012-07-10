<?php	

	if (!App::import('Vendor', 'tcpdf/tcpdf')) { die(); };
	if (!App::import('Vendor', 'fpdi/fpdi')) { die(); };
	
	/**
	 * Component that encapsulates the creation of a tcpdf/fpdi object.
	 * For reference, these are the tags supported by the writeHTML of the tcpdf object:
	 *     a, b, blockquote, br, dd, del, div, dl, dt, em, font, h1, 
	 *     h2, h3, h4, h5, h6, hr, i, img, li, ol, p, small, span, strong, 
	 *     sub, sup, table, td, th, tr, u, ul
	 *
	 * Useful methods of the tcpdf object:
	 *		
	 * 		Cell(
	 * 			width, 
	 * 			height, 
	 * 			text, 
	 * 			border = 0, //can be a combination of LRTB
	 * 			ln = 1, //this is whether to go the next line (1) or stay on the line (0)
	 * 			align = L, //can be L,C,R,J 
	 * 			fill = 0 
	 * 		)
	 *
	 * 		MultiCell(
	 * 			width, 
	 * 			height, 
	 * 			text, 
	 * 			border = 0, //can be a combination of LRTB
	 * 			align = J, //can be L,C,R,J 
	 * 			fill = 0, 
	 * 			ln = 1, //this is whether to go the next line (1) or stay on the line (0)
	 * 			x,
	 * 			y
	 * 		)
	 *
	 * 		getPage() //gets current page number
	 * 		setPage(page, resetMargins = false) //sets the current page number
	 * 		getX()
	 * 		getY()
	 * 		setXY()
	 * 		lastPage() //moves position to last page
	 * 		Ln(height = '') //line break of specified height (blank is height of previous cell)
	 * 		getMargins() //returns an array with ('left', 'right', 'top', 'bottom'. 'header', 'footer', 'cell')
	 * 		{nb} - you can put this string in your pdf and it will be substituted with the total number of pages.
	 */
	class PdfComponent extends Object
	{
		/**
		 * Array of named colors that can be used in the coloredText method.
		 */
		var $colors = array(
			'black' => array(0, 0, 0),
			'darkBlue' => array(46, 60, 134),
			'white' => array(255, 255, 255)
		);
		
		/**
		 * Used internally as a scratch pad of sorts.
		 * @access private
		 */
		var $scratch = null;
		
		function create($title = '', $subject = '', $pageSize = PDF_PAGE_FORMAT)
		{			
			//create new PDF document
			$pdf = new DefaultPdf(PDF_PAGE_ORIENTATION, PDF_UNIT, $pageSize, true); 
			
			//set document information
			$pdf->SetCreator(PDF_CREATOR);
			$pdf->SetAuthor("eMRS");
			$pdf->setPDFVersion('1.5');
			
			if ($title != '')
			{
				$pdf->SetTitle($title);
			}
			
			if ($subject != '')
			{
				$pdf->SetSubject($subject);
			}
			
			//set default header data
			//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);
			
			// set header and footer fonts
			$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
			$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
			
			//set margins and padding
			$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
			$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
			$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
			$pdf->SetCellPadding(0);
			
			//double space bulleted lists
			$pdf->setHtmlVSpace(array(
				'li' => array(0 => array('h' => 7, 'n' => 1))
			));
			
			//set auto page breaks
			$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
			
			//set image scale factor
			$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 
			
			//set some language-dependent strings
			$pdf->setLanguageArray(array(
				'w_page' => 'Page',
				'a_meta_charset' => 'UTF-8',
				'a_meta_dir' => 'ltr',
				'a_meta_language' => 'en'
			)); 
			
			//initialize document
			$pdf->AliasNbPages();
			$pdf->SetFont("times", "", 10);
			
			return $pdf;
		}
		
		/**
		 * Prints out a tabular row of data.
		 * @param object $pdf The tcpdf object to operate on.
		 * @param array $row An array of cells to render in the row. Each cell can be either
		 * a single string, which is simply output, or it can be an array where the first element
		 * is the string to write, and the second element is an options array. The supported keys
		 * in the options array are:
		 * 		style - Anything specified here will be passed along to the style argument of the tcpdf->SetFont method.
		 * 		border - Anything specified here will be passed along to the border argument of the tcpdf->Cell method.
		 * 		align - Anything specified here will be passed along to the border argument of the tcpdf->Cell method.
		 * 		color - The color of the text to render.
		 * 		fillColor - The fill color of the text to render.
		 * @param array $widths An array of cell widths. If omitted, equal cell widths are automatically calculated
		 * based on the size of the pdf document.
		 * @param array $rowStyle An array of styles to apply to every cell in the row. Supports the same keys as the 
		 * options for a single cell.
		 * @param mixed A callback function that will be passed this component and the PDF object if a new page is added 
		 * during the creation of the row. Can be a string or an array containing the object to invoke the method against 
		 * and the method in the object to invoke. The function will be invoked immediately after adding the new page but 
		 * before rendering the row.
		 * @param array $heights Used internally. Do not use.
		 */
		function row($pdf, $row, $widths = null, $rowStyle = array(), $newPageCallback = null, &$heights = null)
		{
			/*
			 wow is this rediculous that we have to do this, but there seems to be a bug in tcpdf that we just can't
			 get around without doing this... esentially tcpdf screws up alignment on a MultiCell that spans pages
			 when you specify $ln = 0 as an argument so that it moves to the right instead of down after the call.
			 This causes the content of the next page to 1. be aligned too far to the right, and 2. text on the first
			 line of the next page overlaps text that was written by the MultiCell call that spanned pages.
			 So... to get around this, essentially I use a "scratchpad" PDF that I write the row to at the exact same
			 spot that we were going to write on the real PDF. If I see that it ended up spanning pages, I advance
			 the page on the real PDF to prevent the MultiCell call from having to span pages. We have to do it this
			 way because there's no way to see the height that a MultiCell call would take up without actually rendering
			 it first.
			*/
			if ($this->scratch == null && $pdf->getAutoPageBreak())
			{
				//Configure::write('debug', 2);
				
				//create a scratchpad and render the row to it (and we'll get the heights of all the cells
				//that are rendered while we're at it)
				$this->scratch = $this->create();
				$this->scratch->setPageOrientation($pdf->getPageOrientation());
				$this->scratch->AddPage();
				$this->scratch->SetX($pdf->GetX());
				$this->scratch->SetY($pdf->GetY(), false);
				$this->scratch->SetFont($pdf->getFontFamily(), $pdf->getFontStyle(), $pdf->getFontSizePt());
				
				$realHeights = array();
				$this->row($this->scratch, $row, $widths, $rowStyle, null, $realHeights);

				//see if we went to a new page on the scratchpad
				if ($this->scratch->getNumPages() > 1)
				{
					//if we did, add a page to the real pdf
					$pdf->AddPage();
					
					//invoke the new page callback if we have one
					if ($newPageCallback != null)
					{
						$tmp = $this->scratch;
						$this->scratch = null;
						
						if (is_string($newPageCallback))
						{
							call_user_func($newPageCallback, $this, $pdf);
						}
						else if (is_object($newPageCallback[0]))
						{						
							$target = array_splice($newPageCallback, 0, 2);
							call_user_func_array($target, array_merge(array($this, $pdf), $newPageCallback));
						}
						else
						{
							$target = array_shift($newPageCallback);
							call_user_func_array($target, array_merge(array($this, $pdf), $newPageCallback));
						}
						
						$this->scratch = $tmp;
					}
				}
				
				//now that we have the heights, let's make each cell the same vertical height by adding
				//line feeds to each cell until they all equal the max vertical height. We do this so if you
				//specify fill colors, the fill will reach the whole vertical height of the longest cell for all 
				//cells in the row
				$max = max($realHeights);
				
				for ($i = 0; $i < count($row); $i++)
				{
					while ($realHeights[$i] < $max)
					{
						//when we append the line feed to the row to make it taller, we need the non-breaking space
						//in the new row as well or TCPDF will not render the line
						if (is_array($row[$i]))
						{
							$row[$i][0] .= "\n" . chr(160);
						}
						else
						{
							$row[$i] .= "\n" . chr(160);
						}
						
						$realHeights[$i]++;
					}
				}

				$this->scratch = null;
			}
			
			//calculate widths if we don't have them
			if ($widths == null)
			{
				$widths = array();
				
				//if we only have one cell in the row, the width is zero (which means to use the whole line)
				if (count($row) == 1)
				{
					$widths[] = 0;
				}
				else
				{
					//determine the page width, accounting for margins
					$margins = $pdf->getMargins();
					$width = $pdf->getPageWidth() - ($margins['left'] + $margins['right']);

					//make each cell have an equal width
					foreach ($row as $cell)
					{
						$widths[] = $width / count($row);
					}
				}
			}
			
			//by default the max height of the row is the height of 1 line of text
			$maxHeight = $pdf->getFontSize() * $pdf->getCellHeightRatio();
			
			//now go through the cells and render them
			for ($i = 0; $i < count($row); $i++)
			{
				$cell = $row[$i];
				$cellsRendered = 0;
				$data = '';
				$options = $rowStyle;
				
				//grab the data and options to render out of the cell
				if (is_array($cell))
				{
					$data = $cell[0];
					$options = array_merge($options, $cell[1]);
				}
				else
				{
					$data = $cell;
				}
					
				//if the cell is empty, let's put a single space to prevent
				//odd height problems with TCPDF
				if ($data === null || trim($data) == '')
				{
					$data = ' ';
				}
				
				//see what options we have
				$style = ifset($options['style'], '');
				$border = ifset($options['border'], '');
				$align = ifset($options['align'], 'L');
				$color = ifset($options['color'], null);
				$fill = ifset($options['fillColor'], null);
				$previousStyle = '';
					
				//set the font style if we have one
				if ($style != '')
				{
					$previousStyle = $pdf->getFontStyle();
					$pdf->SetFont('', $style);
				}
					
				//set the color if we have one
				if ($color != null)
				{
					//if they used a named color, we'll use that instead of RGB
					if (!is_array($color))
					{
						$color = $this->colors[$color];
					}
					
					$pdf->SetTextColor($color[0], $color[1], $color[2]);
				}
					
				//set the fill color if we have one
				if ($fill != null)
				{
					//if they used a named color, we'll use that instead of RGB
					if (!is_array($fill))
					{
						$fill = $this->colors[$fill];
					}
					
					$pdf->SetFillColorArray($fill);
				}
						 			
				//render the cell
				$cellsRendered = $pdf->MultiCell($widths[$i], 0, $data, $border, $align, $fill != null ? 1 : 0, 0);
					
				//revert color to black if we changed it
				if ($color != null)
				{
					$pdf->SetTextColor($this->colors['black'][0], $this->colors['black'][1], $this->colors['black'][2]);
				}
					
				//revert the fill color to black if we changed it
				if ($fill != null)
				{
					$pdf->SetFillColorArray($this->colors['black']);
				}
					
				//revert the font style if we messed with it
				if ($style != '')
				{
					$pdf->SetFont('', $previousStyle);
				}
				
				//keep track of the cell with the largest height so we know how far down to go
				//at the end of the row
				$maxHeight = max($maxHeight, $pdf->getLastH());
				
				//keep track of how many vertical cells were rendered by the line if necessary
				if ($heights !== null)
				{
					$heights[$i] = $cellsRendered;
				}
			}
			
			//now that the row is rendered, advance the Y by the height of the highest cell
			$pdf->Ln($maxHeight);
		}
		
		/**
		 * Renders two separate columns to the PDF. Each column specified
		 * should be an array whose elements are also arrays. Each element should contain
		 * two strings, one for each cell in the column (think label/value pairs). Alternatively,
		 * either element can be an array, where the first element of the array is a string to render
		 * and the second element is another array with options to pass to the TCPDF rendering methods.
		 * Finally, an element in the column array can also have just 1 element, and in that case, the
		 * value of that element will span both cells of the column.
		 * Ex:
		 * 		$left = array(
		 * 			array('Field 1', 'Value 1'),
		 *			array('Field 2', 'Value 2'),
		 * 			array(
		 * 				array('Field 3', array('style' => 'B', 'border' => 'T')), 
		 * 				'Value 3'
		 *			),
		 * 			array('Some text that will span both cells of the column'),
		 * 			array(
		 * 				array(
		 * 					'Some text that will span both cells of the column',
		 * 					array('style' => 'B', 'border' => 'T')
		 * 				),
		 * 				
		 * 			)
		 * 		);
		 * @param object $pdf The TCPDF object.
		 * @param array $left The left column.
		 * @param array $right The right column.
		 * @param array $cellWidths The widths of the 5 columns - 2 for the left column, 2 for the right
		 * and 1 in the middle for spacing.
		 */
		function columns($pdf, $left, $right, $cellWidths = array(40, 45, 10, 40, 45))
		{
			$startX = $leftX = $rightX = $pdf->GetX();
			$startY = $leftY = $rightY = $pdf->GetY();
			$startPage = $leftPage = $rightPage = $pdf->getNumPages();
			
			//render the left column
			for ($i = 0; $i < count($left); $i++)
			{
				//determine what we have for the left label and value
				$leftLabel = $left[$i][0];
				$leftValue = count($left[$i]) == 2 ? $left[$i][1] : null;
				
				//render the row
				$this->row(
					$pdf,
					$leftValue == null ? array($leftLabel) : array($leftLabel, $leftValue),
					$leftValue == null ? array($cellWidths[0] + $cellWidths[1]) : array($cellWidths[0], $cellWidths[1])
				);
				
				//update our position
				$leftX = $pdf->GetX();
				$leftY = $pdf->GetY();
				$leftPage = $pdf->getNumPages();
			}
			
			//reposition
			$indent = $cellWidths[0] + $cellWidths[1] + $cellWidths[2];
			$pdf->setPage($startPage);
			$pdf->SetX($startX);
			$pdf->SetY($startY);
			
			//indent to the right column
			$pdf->SetLeftMargin($pdf->GetX() + $indent);
			
			//render the right column
			for ($i = 0; $i < count($right); $i++)
			{
				//determine what we have for the right label and value
				$rightLabel = $right[$i][0];
				$rightValue = count($right[$i]) == 2 ? $right[$i][1] : null;

				//render the row
				$this->row(
					$pdf,
					$rightValue == null ? array($rightLabel) : array($rightLabel, $rightValue),
					$rightValue == null ? array($cellWidths[3] + $cellWidths[4]) : array($cellWidths[3], $cellWidths[4])
				);
				
				//update our position
				$rightX = $pdf->GetX();
				$rightY = $pdf->GetY();
				$rightPage = $pdf->getNumPages();
			}
			
			//unindent	
			$pdf->SetLeftMargin($pdf->GetX() - $indent);
				
			//reposition 
			$x = $rightPage > $leftPage ? $rightX : ($leftPage > $rightPage ? $leftX : max($leftX, $rightX));
			$y = $rightPage > $leftPage ? $rightY : ($leftPage > $rightPage ? $leftY : max($leftY, $rightY));
			$page = max($leftPage, $rightPage);
				
			$pdf->SetX($x);
			$pdf->SetY($y);
			$pdf->setPage($page);
		}
		
		/**
		 * Utility method to render a line of colored text.
		 * @param object $pdf The tcpdf object to operate on.
		 * @param string $text The text to render.
		 * @param string $color The color to use. Must be one of the colors defined in this class's colors array.
		 * @param string $fillColor The fill color to use (if any). Must be one of the colors defined in this class's colors array.
		 * @param int $size The size in points, if desired, of the text. Omit or pass zero to use the current font size.
		 * @param string $border The border to place around the text. Anything specified here will be passed along to the border argument of the tcpdf->Cell method.
		 */
		function coloredText($pdf, $text, $color, $fillColor = '', $size = 0, $border = '')
		{
			//set the fill color if we have one
			if ($fillColor != '')
			{
				$pdf->SetFillColorArray($this->colors[$fillColor]);
			}
			
			$previousSize = $pdf->getFontSizePt();
			
			//set the font size if we have one
			if ($size != 0)
			{
				$pdf->SetFontSize($size);
			}
			
			$pdf->SetTextColor($this->colors[$color][0], $this->colors[$color][1], $this->colors[$color][2]);
			$pdf->Cell(0, 0, $text, $border, 1, '', $fillColor != '' ? 1 : 0);
			$pdf->SetTextColor($this->colors['black'][0], $this->colors['black'][1], $this->colors['black'][2]);
			
			//reset font size if necessary
			if ($size != 0)
			{
				$pdf->SetFontSize($previousSize);
			}
			
			//reset the fill color to black when we're done
			if ($fillColor != '')
			{
				$pdf->SetFillColorArray($this->colors['black']);
			}
		}
		
		/**
		 * Utility method to generate a bulleted list out of an array of text items
		 * @param object $pdf The tcpdf object to operate on.
		 * @param array $items An array of strings to render as a bulleted list.
		 */
		function bulletedList($pdf, $items)
		{
			if (count($items) == 0)
			{
				$pdf->Ln(1);
			}
			
			//for some reason bulleted lists have a margin at the top I can't seem to get rid of
			//even when specifying them in the setHtmlVSpace method. So we just move the Y position
			//up a little bit
			$pdf->SetY($pdf->GetY() - 5);
			$pdf->writeHTML('<ul><li>' . implode('</li><li>', $items). '</li></ul>');
		}
	}
	
	/**
	 * Our own derivative of the TCPDF class so we can override certain methods that control
	 * output on the PDF.
	 */
	class DefaultPdf extends TCPDF
	{
		var $topLeftHeader = '';
		var $topRightHeader = '';
		var $headerColor = array(0, 0, 0);
		
		/**
		 * Sets the top left header string for page headers.
		 * @param string $value The string to use.
		 */
		function setTopLeftHeaderString($value)
		{
			$this->topLeftHeader = $value;
		}
		
		/**
		 * Sets the top right header string for page headers.
		 * @param string $value The string to use.
		 */
		function setTopRightHeaderString($value)
		{
			$this->topRightHeader = $value;
		}
		
		/**
		 * Sets the color of the text that is used in the header.
		 * @param array $color A 3 element array containing elements for Red, Green, and Blue consisting of a number
		 * between 0 and 255.
		 */
		function setHeaderColor($color)
		{
			$this->headerColor = $color;
		}
		
		/**
		 * Overridden. Renders the page header.
		 */
		function Header()
		{
			$this->SetTextColorArray($this->headerColor);
			$this->Cell(0, 0, $this->topLeftHeader, 'B', 0);
			$this->Cell(0, 0, $this->topRightHeader, 'B', 0, 'R');
			$this->SetTextColorArray(array(0, 0, 0));
		}
		
		/** Overridden. Adds a page to the document **/
		function AddPage($orientation='', $format='') 
		{
			parent::AddPage($orientation, $format);
			
			//I add an Ln() here to force a zero-height new line. Without it, TCPDF seems 
			//to screw up the left margin for the first line of the new page if you've previously
			//adjusted the left margin. The Ln seems to fix the problem.
			$this->Ln(0);
		}
	}
?>