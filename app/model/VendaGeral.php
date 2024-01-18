<?php

class VendaGeral extends TRecord
{
    const TABLENAME  = 'venda_geral';
    const PRIMARYKEY = 'romaneio_id';
    const IDPOLICY   =  'max'; // {max, serial}

    

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('farmacia_id');
        parent::addAttribute('filial');
        parent::addAttribute('numero_venda');
        parent::addAttribute('valor_venda');
        parent::addAttribute('vendedor_id');
        parent::addAttribute('vendedor');
        parent::addAttribute('catalogo');
        parent::addAttribute('valor_formula');
        parent::addAttribute('forma_pagamento_id');
        parent::addAttribute('forma_pagamento');
        parent::addAttribute('cf');
        parent::addAttribute('data_ini');
        parent::addAttribute('data_fim');
            
    }

    
}

