<?php namespace Pulpitum\Core\Controllers\Admin;

use Pulpitum\Core\Controllers\Admin\BackendController;
use Theme;

class CrudController extends BackendController {



 	public function getView($id){
        return Redirect::to(URL::previous());
        /*$model = $this->entidade->find($id);
        return $this->theme->of('lactiweb::view', array("model" => $model ))->render();*/
    }

    public function getEdit($id){
        $model = $this->entidade->find($id);
        $return = $this->entidade->actionsUrl();
        return $this->theme->of('core::forms.edit', array("model" => $model, "update_rules"=>$this->rules, "create_rules"=>$this->rules, "title"=>$this->getName(), "entidade"=>$this->entidade, "return"=>$return['list']['as'] ))->render();
    }

    public function postEdit($id){
        $entidade = $this->entidade->find($id);
        $validator = Validator::make(Input::all(), $this->rules);
        
        if($validator->fails())
        {
            Session::flash('warning', trans('lactiweb::form.error')."<br />".$validator->messages());

            return Redirect::to(URL::previous())->withErrors($validator)->withInput();
        }
        $data = Input::all();
        $return = Input::get("return");

        foreach ($this->entidade->getCheckboxFields() as $value) {
            if(!array_key_exists($value, $data)){
                $data[$value] = 0;
            }
        }
        $entidade->fill($data);
        try{
            if( $entidade->save() )
            {
                Session::flash('success', trans('lactiweb::form.save-success'));
                if(Input::get("back")!="")
                    return Redirect::to(url(Input::get("back")));
                $return = Input::get("return");
                return Redirect::route($return);
            }else{
                Session::flash('warning', trans('lactiweb::form.try'));
                return Redirect::to(URL::previous())->withInput();
            }
        }catch(\Illuminate\Database\QueryException $e){
                Session::flash('warning', trans('lactiweb::form.error'));
                return Redirect::to(URL::previous())->withInput();
        }
    }

    public function getAdd(){
        $model = $this->entidade;
        $return = $this->entidade->actionsUrl();
        return $this->theme->of('core::forms.edit', array("model" => $model,"update_rules"=>$this->rules, "create_rules"=>$this->rules, "title"=>$this->getName(), "entidade"=>$this->entidade, "return"=>isset($return['list']['as']) ? $return['list']['as'] : "" ))->render();
    }

    public function postAdd(){
        //Valida os dados inseridos não existem.
        $primaryKey = $this->entidade->getPrimaryKey();

        if(is_array($primaryKey)){
            $primaryKeyValues = array();
            foreach ($primaryKey as $key => $value) {
                $primaryKeyValues[] = Input::get($value);
            }
            if(count($primaryKeyValues)>0)
                $entidade = $this->entidade->find($primaryKeyValues);
        }else{
            $entidade = $this->entidade->find(Input::get($primaryKey));
        }

        if(is_null($entidade) ){
            $entidade = $this->entidade;
        }

        $validator = Validator::make(Input::all(), $this->rules);
        
        if($validator->fails())
        {
            Session::flash('warning', trans('lactiweb::form.error'));
            return Redirect::to(URL::previous())->withErrors($validator)->withInput();
        }
        
        $data = Input::all();
        $return = Input::get("return");
        $entidade->fill($data);
        try{
            if( $entidade->save() )
            {
                Session::flash('success', trans('lactiweb::form.save-success'));
                $return = Input::get("return");
                return Redirect::route($return);
            }else{
                Session::flash('warning', trans('lactiweb::form.try'));
                return Redirect::to(URL::previous())->withInput();
            }
        }catch(\Illuminate\Database\QueryException $e){
                Session::flash('warning', trans('lactiweb::form.error'));
                return Redirect::to(URL::previous())->withInput();
        }

    }

    public function getAjaxAdd(){
        $model = $this->entidade;
        $return = $this->entidade->actionsUrl();
        return View::make('core::forms.ajax', array("model" => $model,"update_rules"=>$this->rules, "create_rules"=>$this->rules, "title"=>$this->getName(), "entidade"=>$this->entidade, "return"=>isset($return['list']['as']) ? $return['list']['as'] : "" ))->render();
    }

    public function postAjaxAdd(){
 
        //Valida os dados inseridos não existem.
        $primaryKey = $this->entidade->getPrimaryKey();
        $entidade = null;
        if(is_array($primaryKey)){
            $primaryKeyValues = array();
            foreach ($primaryKey as $key => $value) {
                $get = Input::get($value);
                if( !empty($get) )
                    $primaryKeyValues[] = $get;
            }

            if(count($primaryKeyValues) == count($primaryKey) )
                $entidade = $this->entidade->find($primaryKeyValues)->first();
        }else{
            $entidade = $this->entidade->find(Input::get($primaryKey))->first();
        }

        if( is_null($entidade) or (isset($entidade) && $entidade->count() == 0) ){
            $entidade = $this->entidade;
        }

        $validator = Validator::make(Input::all(), $this->rules);
        
        if($validator->fails())
        {
            $response = array(
                'status' => 'error',
                'msg' => trans('lactiweb::form.error'),
            );
            return Response::json( $response );
        }
        
        $data = Input::all();
        $entidade->fill($data);

        try{
            
            if( $entidade->save() ){
                $response = array(
                    'status' => 'success',
                    'msg' => trans('lactiweb::form.save-success'),
                );
            }else{

                $response = array(
                    'status' => 'error',
                    'msg' => trans('lactiweb::form.try'),
                );

            }

        }catch(\Illuminate\Database\QueryException $e){
            $response = array(
                'status' => 'error',
                'msg' => trans('lactiweb::form.try'),
            );
        }

        return Response::json( $response );
    }

    /*
     * Delete row
    */
    public function getDelete($id){

        try{
            $entidade = $this->entidade->find($id);
            $entidade->delete();
            Session::flash('success', trans('core::all.messages.delete-success'));
        }catch (\Exception $e){
            Session::flash('warning', trans('core::all.messages.not-found'));
        }

        return Redirect::to(URL::previous());
    }

    
}	