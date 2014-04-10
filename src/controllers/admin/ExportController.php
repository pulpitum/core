<?php namespace Pulpitum\Core\Controllers\Admin;

use Pulpitum\Core\Controllers\Admin\BackendController;
use Input;
use Sentry;
use Redirect;
use Config;
use Response;
use DB;
use Request;
use Theme;
use App;
use Pulpitum\Core\Models\Helpers\Tools as Tools;
use Excel;
use View;
use \PHPExcel_Shared_File;
use \PDFMerger;
use URL;

class ExportController extends BackendController {


	public function getExport($model){
		$data = $this->getEntidade($model);
		$rows = $this->getData($data)->get();
		//Get Urls
		$action_url = $data->actionsUrl();
		$check = Sentry::check();
		$output = $this->prepData($rows, $data, $action_url, $check, true);
		setcookie('fileDownload', "true", false, '/');
		try{
			PHPExcel_Shared_File::setUseUploadTempDirectory(true);
			Excel::loadView('datatables::excel', array('data' => $data, 'rows'=>$output))->setTitle($data->getEntidadeTitle())->export("xls");
		}catch(\Exception $e){
			\Log::info( $e->getMessage() );
		}
	}

	public function getPrint($model){
		$entidade = $this->getEntidade($model);
		$items_per_page = $entidade->items_per_page;
		$count = $this->getTotalResults($entidade)->count();

		if($count==0){
			return "No Data";
		}
		$path = $_SERVER['DOCUMENT_ROOT']."/".Theme::path().'/assets/pdfs/';
		if(!file_exists($path))
			mkdir($path);

		$random_file_name = str_random(10);
		$pages_number = ceil($count / $items_per_page);
		$action_url = $entidade->actionsUrl();
		$check = Sentry::check();
		$logo = $_SERVER['DOCUMENT_ROOT'].'/'.Theme::path().'/assets/'.Theme::asset()->path('img/logotipo.png');
		$model = $entidade->getEntidadeTitle();

		$filters = Input::get('filters');

		if($filters != '')
			$filters = 'Filtros: '.$filters;

		$data = $this->getData($entidade);
		
		$PDFMerger = new PDFMerger;
		for($i=0; $i<$pages_number; $i++){
			$pdf = App::make('dompdf');
			$pdf->setPaper('a4')->setOrientation('landscape');
			$rows = $data->take($items_per_page)->skip($i)->get();
			$output = $this->prepData($rows, $entidade, $action_url, $check, true);

			$html = View::make('datatables::excel', array('data' => $entidade, 'rows'=>$output));
			$extras =       '<script type="text/php">
			                        if ( isset($pdf) ) {
			                            //Fonts
		                                $font = Font_Metrics::get_font("helvetica", "bold");
		                                $font_filter = Font_Metrics::get_font("helvetica", "normal");                                           

		                                //Header
			                            $x = 710;
			                            $y = 8;
			                            $img_w = 100;
			                            $img_h = 35;                                                
			                            $header = $pdf->open_object();
			                            $pdf->image("'.$logo.'", $x, $y, $img_w, $img_h);
			                            $pdf->close_object();
			                            $pdf->add_object($header);
		                                $pdf->page_text(35,20, "Listagem: '.$model.'", $font, 10, array(0,0,0));
		                                $pdf->page_text(35,32, "'.$filters.'", $font_filter, 8, array(0,0,0));
		                                
		                                //Footer
		                                $pdf->page_text(770,570, "Pag: '.($i+1).' de '.$pages_number.'", $font, 6, array(0,0,0));
		                                $pdf->page_text(35,570, "'.date('d-m-Y | h:i A').'", $font, 6, array(0,0,0));
		                                $pdf->page_text(350,570, "Lactiweb: '.Config::get('lactiweb::version') .' | Utilizador: '.Sentry::getUser()->first_name. ' ' . Sentry::getUser()->last_name .'", $font, 6, array(0,0,0));
			                        }
			                </script>';
			$pdf_data ='<html>'; 
			$pdf_data .='<head>';
			$pdf_data .='<meta charset=utf-8"/>';
			$pdf_data .='<link rel="stylesheet" type="text/css" media="screen" href="'.Theme::asset()->url('css/pdf.css').'" />';
			$pdf_data .='</head>';
			$pdf_data .='<body>';
			$pdf_data .= $extras;
			$pdf_data .= $html;
			$pdf_data .='</body>';
			$pdf_data .='</html>';
			$filename = $path.$random_file_name.'_'.$i.'.pdf';

			$pdf->loadHTML($pdf_data);
			$pdf->save($filename);
			$PDFMerger->addPDF($filename, 'all');

		}
		$PDFMerger->merge('browser', $path.$random_file_name.'_final.pdf');
		array_map('unlink', glob($path.$random_file_name.'_*'));
		return "ok";
	}


}