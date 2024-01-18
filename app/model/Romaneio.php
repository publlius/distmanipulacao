<?php

class Romaneio extends TRecord
{
    const TABLENAME  = 'romaneio';
    const PRIMARYKEY = 'numero_venda';
    const IDPOLICY   =  'serial'; // {max, serial}

    private $farmacia;

    

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('farmacia_id');
        parent::addAttribute('id');
        parent::addAttribute('cliente');
        parent::addAttribute('emissao_venda');
        parent::addAttribute('previsao_entrega');
        parent::addAttribute('previsao_entrega_hora');
        parent::addAttribute('valor_venda');
        parent::addAttribute('valor_entrada');
        parent::addAttribute('valor_saldo');
            
    }

    /**
     * Method set_system_unit
     * Sample of usage: $var->system_unit = $object;
     * @param $object Instance of SystemUnit
     */
    public function set_farmacia(SystemUnit $object)
    {
        $this->farmacia = $object;
        $this->farmacia_id = $object->id;
    }

    /**
     * Method get_farmacia
     * Sample of usage: $var->farmacia->attribute;
     * @returns SystemUnit instance
     */
    public function get_farmacia()
    {
        TTransaction::open('permission');
        // loads the associated object
        if (empty($this->farmacia))
            $this->farmacia = new SystemUnit($this->farmacia_id);
        TTransaction::close();
        // returns the associated object
        return $this->farmacia;
    }

    /**
     * Method getVendas
     */
    public function getVendas()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('romaneio_id', '=', $this->id));
        return Venda::getObjects( $criteria );
    }

    
}

