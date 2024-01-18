<?php

class Situacao extends TRecord
{
    const TABLENAME  = 'situacao';
    const PRIMARYKEY = 'id';
    const IDPOLICY   =  'serial'; // {max, serial}

    

    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('descricao');
            
    }

    /**
     * Method getRomaneios
     */
    public function getRomaneios()
    {
        $criteria = new TCriteria;
        $criteria->add(new TFilter('situacao_id', '=', $this->id));
        return Romaneio::getObjects( $criteria );
    }

    
}

