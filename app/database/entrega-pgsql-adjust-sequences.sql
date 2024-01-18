SELECT setval('estoque_id_seq', coalesce(max(id),0) + 1, false) FROM estoque;
SELECT setval('farmacia_id_seq', coalesce(max(id),0) + 1, false) FROM farmacia;
SELECT setval('forma_pagamento_id_seq', coalesce(max(id),0) + 1, false) FROM forma_pagamento;
SELECT setval('romaneio_id_seq', coalesce(max(id),0) + 1, false) FROM romaneio;
SELECT setval('situacao_id_seq', coalesce(max(id),0) + 1, false) FROM situacao;
SELECT setval('vendendor_id_seq', coalesce(max(id),0) + 1, false) FROM vendendor;