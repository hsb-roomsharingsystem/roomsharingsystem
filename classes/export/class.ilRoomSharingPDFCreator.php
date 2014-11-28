<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * Klasse zur Erstellung von PDF-Dateien aus HTML
 *
 * @author MartinDoser
 */
class ilRoomSharingPDFCreator
{
	/**
	 * Methode zur Erstellung von PDFs
	 * output modes:
	 * PDF_OUTPUT_DOWNLOAD = 'D'
	 * PDF_OUTPUT_INLINE = 'I'
	 * PDF_OUTPUT_FILE = 'F'
	 *
	 * @param type $html_input
	 * @param type $output_mode
	 * @param string $filename
	 */
	public static function generatePDF($html_input, $output_mode, $filename)
	{
		$html_input = self::preprocessHTML($html_input);

		if (substr($filename, (strlen($filename) - 4), 4) != '.pdf')
		{
			$filename .= '.pdf';
		}

		require_once 'Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/classes/export/class.ilRoomSharingPDFGeneration.php';

		$job = new ilPDFGenerationJob();
		$job->setAutoPageBreak(true)
			->setCreator('RoomSharing Export Test')
			->setFilename($filename)
			->setMarginLeft('20')
			->setMarginRight('20')
			->setMarginTop('20')
			->setMarginBottom('20')
			->setOutputMode($output_mode)
			->addPage($html_input);

		ilRoomSharingPDFGeneration::doJob($job);
	}

	/**
	 * Arbeitet css-Files ein html ein.
	 *
	 * @param type $html
	 * @return type
	 */
	public static function preprocessHTML($html)
	{
		$pdf_css_path = self::getTemplatePath('appointments_pdf.css');
		return '<style>' . file_get_contents($pdf_css_path) . '</style>' . $html;
	}

	/**
	 * Returns the path of the css-File
	 *
	 * @param type $a_filename
	 * @return string
	 */
	protected static function getTemplatePath($a_filename)
	{
		$module_path = "Customizing/global/plugins/Services/Repository/RepositoryObject/RoomSharing/";

		include_once "Services/Style/classes/class.ilStyleDefinition.php";
		if (ilStyleDefinition::getCurrentSkin() != "default")
		{
			$fname = "./Customizing/global/skin/" .
				ilStyleDefinition::getCurrentSkin() . "/" . $module_path . basename($a_filename);
		}
		if ($fname == "" || !file_exists($fname))
		{
			$fname = "./" . $module_path . "templates/default/" . basename($a_filename);
		}

		return $fname;
	}

}
