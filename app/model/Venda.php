<?php

class Venda extends TRecord
{
    const TABLENAME  = 'venda';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}

    private $forma_pagamento;
    private $entregue_por;
    private $vendido_por;
    private $romaneio;

    

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('romaneio_id');
        parent::addAttribute('vendido_por_id');
        parent::addAttribute('entregue_por_id');
        parent::addAttribute('catalogo');
        parent::addAttribute('cf');
        parent::addAttribute('numero_formula');
        parent::addAttribute('data_venda');
        parent::addAttribute('valor_formula');
        parent::addAttribute('desconto');
        parent::addAttribute('valor_venda');
        parent::addAttribute('forma_pagamento_id');
        parent::addAttribute('observacao');
        parent::addAttribute('criado_em');
        parent::addAttribute('criado_por');
        parent::addAttribute('atualizado_em');
        parent::addAttribute('atualizado_por');
            
    }

    /**
     * Method set_forma_pagamento
     * Sample of usage: $var->forma_pagamento = $object;
     * @param $object Instance of FormaPagamento
     */
    public function set_forma_pagamento(FormaPagamento $object)
    {
        $this->forma_pagamento = $object;
        $this->forma_pagamento_id = $object->id;
    }

    /**
     * Method get_forma_pagamento
     * Sample of usage: $var->forma_pagamento->attribute;
     * @returns FormaPagamento instance
     */
    public function get_forma_pagamento()
    {
    
        // loads the associated object
        if (empty($this->forma_pagamento))
            $this->forma_pagamento = new FormaPagamento($this->forma_pagamento_id);
    
        // returns the associated object
        return $this->forma_pagamento;
    }
    /**
     * Method set_system_users
     * Sample of usage: $var->system_users = $object;
     * @param $object Instance of SystemUsers
     */
    public function set_entregue_por(SystemUsers $object)
    {
        $this->entregue_por = $object;
        $this->entregue_por_id = $object->id;
    }

    /**
     * Method get_entregue_por
     * Sample of usage: $var->entregue_por->attribute;
     * @returns SystemUsers instance
     */
    public function get_entregue_por()
    {
        TTransaction::open('permission');
        // loads the associated object
        if (empty($this->entregue_por))
            $this->entregue_por = new SystemUsers($this->entregue_por_id);
        TTransaction::close();
        // returns the associated object
        return $this->entregue_por;
    }
    /**
     * Method set_system_users
     * Sample of usage: $var->system_users = $object;
     * @param $object Instance of SystemUsers
     */
    public function set_vendido_por(SystemUsers $object)
    {
        $this->vendido_por = $object;
        $this->vendido_por_id = $object->id;
    }

    /**
     * Method get_vendido_por
     * Sample of usage: $var->vendido_por->attribute;
     * @returns SystemUsers instance
     */
    public function get_vendido_por()
    {
        TTransaction::open('permission');
        // loads the associated object
        if (empty($this->vendido_por))
            $this->vendido_por = new SystemUsers($this->vendido_por_id);
        TTransaction::close();
        // returns the associated object
        return $this->vendido_por;
    }
    /**
     * Method set_romaneio
     * Sample of usage: $var->romaneio = $object;
     * @param $object Instance of Romaneio
     */
    public function set_romaneio(Romaneio $object)
    {
        $this->romaneio = $object;
        $this->romaneio_id = $object->id;
    }

    /**
     * Method get_romaneio
     * Sample of usage: $var->romaneio->attribute;
     * @returns Romaneio instance
     */
    public function get_romaneio()
    {
    
        // loads the associated object
        if (empty($this->romaneio))
            $this->romaneio = new Romaneio($this->romaneio_id);
    
        // returns the associated object
        return $this->romaneio;
    }

    
}

