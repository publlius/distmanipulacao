begin; 

PRAGMA foreign_keys=OFF; 

CREATE TABLE estoque( 
      id  INTEGER    NOT NULL  , 
      numero_formula int  (10)   , 
      cliente varchar  (240)   , 
      data_emissao date   , 
      previsao_entrega date   , 
      valor double  (10,2)   , 
      situacao_id int   NOT NULL  , 
 PRIMARY KEY (id),
FOREIGN KEY(situacao_id) REFERENCES situacao(id)); 

 CREATE TABLE farmacia( 
      id  INTEGER    NOT NULL  , 
      descricao varchar  (200)   NOT NULL  , 
 PRIMARY KEY (id)); 

 CREATE TABLE forma_pagamento( 
      id  INTEGER    NOT NULL  , 
      descricao char  (20)   NOT NULL  , 
 PRIMARY KEY (id)); 

 CREATE TABLE romaneio( 
      id  INTEGER    NOT NULL  , 
      farmacia_id int   NOT NULL  , 
      numero_venda char  (10)   , 
      cliente varchar  (200)   , 
      emissao_venda date   , 
      previsao_entrega date   , 
      previsao_entrega_hora text   , 
      valor_venda double  (10,2)   , 
      valor_entrada double  (10,2)   , 
 PRIMARY KEY (id),
FOREIGN KEY(farmacia_id) REFERENCES farmacia(id)); 

 CREATE TABLE situacao( 
      id  INTEGER    NOT NULL  , 
      descricao varchar  (100)   , 
 PRIMARY KEY (id)); 

 CREATE TABLE vendendor( 
      id  INTEGER    NOT NULL  , 
      nome varchar  (200)   NOT NULL  , 
 PRIMARY KEY (id)); 

 
  
 
 commit;