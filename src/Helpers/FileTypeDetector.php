<?php

namespace Maatwebsite\Excel\Helpers;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;

// @todo: RRE - Add the Helper IOFactory::identify($inputFileName)
use PhpOffice\PhpSpreadsheet\IOFactory;

class FileTypeDetector
{
    /**
     * @param             $filePath
     * @param string|null $type
     *
     * @throws NoTypeDetectedException
     * @return string|null
     */
    public static function detect($filePath, string $type = null)
    {

	    if (null !== $type) {
            return $type;
        }
        if (!$filePath instanceof UploadedFile) {
            $pathInfo  = pathinfo($filePath);
            $extension = $pathInfo['extension'] ?? '';
        } else {
            $extension = $filePath->getClientOriginalExtension();
        }
        if (null === $type && trim($extension) === '') {
            throw new NoTypeDetectedException();
        }
        return config('excel.extension_detector.' . strtolower($extension));

		/*
		 * @todo: RRE
		 * NOTE: 
		 *	This validation could cause an error, and the FilePath (File) has
		 *  a different file format or it is incompatible with the "Reader".
		 *
		 *  In blind processes multiple files formats could be accepted.
		 *	- The programmer needs to know the available options -
		 *	Decision Tree
		 *  | File Extension | File Content | Type Provided |
		 *
		 * If No "Type" Provided - "File Content" could take Priority over "File Extension" 
		*/

		// @todo: RRE - recommendation to change 
        // if (null !== $type) {
        //    return $type;
        // }
		
		try {
			$fileContentType = IOFactory::identify($filePath);
		} catch ( \InvalidArgumentException $ex) {
			// 
			// File NOT FOUND 
			//
			// Log the Error if a Log Tracking is implemented
			//
			// echo 'Code: '. $ex->getCode() . ' ' . $ex->getMessage();
			// dd('Code: '. $ex->getCode() . ' ' . $ex->getMessage());
		} catch ( \PhpOffice\PhpSpreadsheet\Reader\Exception $ex ) {
			// Log the Error if a Log Tracking is implemented
			// In the CALLING Instance -> This Exception 
			// 		will Create a Maatwebsite\Excel\Exceptions\NoTypeDetectedException
			// dd($ex->getMessage());
		} finally {
			if( ! isset($fileContentType) ){
				/*
				 * There is NO Point to continue, The Read will FAIL!
				 * 	The file does NOT contain a valid format to be used by phpSpreadsheet
				 *	Two Options:
				 *		- Throw Exception
				 *		- Return NULL to be able to continue with the process
				*/
				throw new NoTypeDetectedException();
				// return null;
				// dd('Stop Error!');
			}
		}

		/*
		 * @todo: remove this section of code
		 * The previous code will take care of the Extension analysis.
		 * NOTE:
		 *	Keep this code in case of it is necessary to validate
		 *	EXTERNAL Extension with INTERNAL Content Format
		 * Recommendation:
		 *	RETURN INTERNAL Content Format if NOT Exception is raised.
		 *
		*/
        if (!$filePath instanceof UploadedFile) {
            $pathInfo  = pathinfo($filePath);
            $extension = $pathInfo['extension'] ?? '';
        } else {
            $extension = $filePath->getClientOriginalExtension();
        }
		// dd('Type: ' .  $type . ' <-Extension: ' . $extension . ' <-Content Type: ' . $fileContentType . ' <-FilePath: ' . $filePath);
		
		/*
		 * @todo: Log any extension discrepancies with the Content Type
		 *
		*/
		if ( null === $type ){
			$type = $fileContentType;
		}
		if ($type !== $fileContentType) {
			// Log Type requested vs Content Type
			// @todo: Create a new Exception
            // throw new TypeMismatchDetectedException();
		}
		if ($extension !== $fileContentType ) {
			// Log Type requested vs Content Type			
			// @todo: Create a new Exception
            // throw new TypeMismatchDetectedException();
		}

		// @todo: Check if necessary
		// Previous ( null === $type ) will void this condition
		// $fileContentType value could void the NO Extension and allow accept files without Extension.
		// Exception is used while the IOFactory::identify is executed
		//
        if (null === $type && trim($extension) === '') {
            throw new NoTypeDetectedException();
        }

		/*
		 * @todo: RRE -
		 *    Return the READABLE Value of the file
		 *
		*/
        // return config('excel.extension_detector.' . strtolower($extension));
        return config('excel.extension_detector.' . strtolower($fileContentType));
    }

    /**
     * @param string      $filePath
     * @param string|null $type
     *
     * @throws NoTypeDetectedException
     * @return string
     */
    public static function detectStrict(string $filePath, string $type = null): string
    {
        $type = static::detect($filePath, $type);

        if (!$type) {
            throw new NoTypeDetectedException();
        }

        return $type;
    }
}
