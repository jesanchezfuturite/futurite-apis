<?php

namespace App\Http\Controllers;

use App\Repositories\CustomersRepositoryEloquent;

use Illuminate\Http\Request;

class ConfigController extends Controller
{
    //repositorios
    protected $customers;
    

    //construcor esta creando una instancia customers
    public function __construct(
        CustomersRepositoryEloquent $customers
    )
    {
        $this->customers = $customers;
    }

    //crear un metodo que enliste los customers del api
    /**
     * 
     * este metodo sirve para enlistar los customers que obtuvimos del API GOOGLE ADS
     * 
     * @param any
     * 
     * @return un arreglo con los customer y clientes de futurite a la vista
     * 
     * 
     */
    public function listCustomers()
    {
        try{

            //1 
            $customersList = $this->customers->all();

            $info = [
                "customerList" => $customersList
            ];
            return view('config-customers',$info);

           //  dd($customersList);

        }catch(\Exception $e)
        {
            dd($e);
        }
    }


}