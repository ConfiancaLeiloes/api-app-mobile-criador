ALTER DATABASE gc_confianca_criador CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE tab_animais CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
ALTER TABLE tab_pessoas CONVERT TO CHARACTER SET utf8 COLLATE utf8_general_ci;
 
SELECT
  *
FROM
  (
    (
      SELECT
        tab_animais.id_animal AS ID_ANIMAL,
        tab_grupo_animais.descricao AS GRUPO_ANIMAL,
        tab_animais.nome AS NOME_ANIMAL,
        IF(
          ISNULL(tab_socios.cotas_socio_01),
          '0.00',
          tab_socios.cotas_socio_01
        ) AS COTAS_ANIMAL,
        tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
        tab_pai_animal.nome AS PAI_ANIMAL,
        tab_mae_animal.nome AS MAE_ANIMAL,
        tab_animais.registro_associacao AS REGISTRO_ANIMAL,
        CONCAT(
          tab_pessoas.nome_razao_social,
          '
            Permanência: ',
          DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
          IF(
            tab_socios.adm_01 = '3',
            '
            Sócio Administrador',
            ''
          )
        ) AS NOME_SOCIO,
        IF(
          ISNULL(tab_socios.cotas_socio_01),
          '0.00',
          tab_socios.cotas_socio_01
        ) AS COTAS_SOCIO,
        tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
        tab_cidades.nome_cidade AS CIDADE_SOCIO,
        tab_estados.sigla_estado AS ESTADO_SOCIO,
        CONCAT(
          tab_pessoas.telefone_celular,
          ' ',
          tab_pessoas.telefone_fixo
        ) AS TELEFONE_SOCIO,
        tab_pessoas.email_usuario AS EMAIL_SOCIO,
        tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
        tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
      FROM
        tab_animais
        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
        JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
        JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
        LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
        LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_01
        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
      WHERE
        tab_animais.id_tipo_animal = '10'
        AND tab_animais.id_grupo = '1'
        AND tab_animais.id_situacao = '1'
        AND (
          tab_animais.nome LIKE '%%'
          OR tab_animais.marca LIKE '%%'
          OR tab_animais.registro_associacao LIKE '%%'
          OR tab_grupo_animais.descricao LIKE '%%'
          OR tab_pai_animal.nome LIKE '%%'
          OR tab_mae_animal.nome LIKE '%%'
          OR tab_animais.chip LIKE '%%'
          OR tab_socios.informacoes_diversas LIKE '%%'
          OR tab_animais.informacoes_diversas LIKE '%%'
        )
        AND tab_animais.id_usuario_sistema = '8301'
      GROUP BY
        tab_animais.id_animal
      ORDER BY
        tab_animais.nome ASC
    )
    UNION
    (
      SELECT
        tab_animais.id_animal AS ID_ANIMAL,
        tab_grupo_animais.descricao AS GRUPO_ANIMAL,
        tab_animais.nome AS NOME_ANIMAL,
        IF(
          ISNULL(tab_socios.cotas_socio_01),
          '0.00',
          tab_socios.cotas_socio_01
        ) AS COTAS_ANIMAL,
        tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
        tab_pai_animal.nome AS PAI_ANIMAL,
        tab_mae_animal.nome AS MAE_ANIMAL,
        tab_animais.registro_associacao AS REGISTRO_ANIMAL,
        CONCAT(
          tab_pessoas.nome_razao_social,
          '
            Permanência: ',
          DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
          IF(
            tab_socios.adm_02 = '3',
            '
            Sócio Administrador',
            ''
          )
        ) AS NOME_SOCIO,
        IF(
          ISNULL(tab_socios.cotas_socio_02),
          '0.00',
          tab_socios.cotas_socio_02
        ) AS COTAS_SOCIO,
        tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
        tab_cidades.nome_cidade AS CIDADE_SOCIO,
        tab_estados.sigla_estado AS ESTADO_SOCIO,
        CONCAT(
          tab_pessoas.telefone_celular,
          ' ',
          tab_pessoas.telefone_fixo
        ) AS TELEFONE_SOCIO,
        tab_pessoas.email_usuario AS EMAIL_SOCIO,
        tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
        tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
      FROM
        tab_animais
        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
        JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
        JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
        LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
        LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_02
        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
      WHERE
        tab_animais.id_tipo_animal = '10'
        AND tab_animais.id_grupo = '1'
        AND tab_animais.id_situacao = '1'
        AND (
          tab_animais.nome LIKE '%%'
          OR tab_animais.marca LIKE '%%'
          OR tab_animais.registro_associacao LIKE '%%'
          OR tab_grupo_animais.descricao LIKE '%%'
          OR tab_pai_animal.nome LIKE '%%'
          OR tab_mae_animal.nome LIKE '%%'
          OR tab_animais.chip LIKE '%%'
          OR tab_socios.informacoes_diversas LIKE '%%'
          OR tab_animais.informacoes_diversas LIKE '%%'
        )
        AND tab_animais.id_usuario_sistema = '8301'
      GROUP BY
        tab_animais.id_animal
      ORDER BY
        tab_animais.nome ASC
    )
    UNION
    (
      SELECT
        tab_animais.id_animal AS ID_ANIMAL,
        tab_grupo_animais.descricao AS GRUPO_ANIMAL,
        tab_animais.nome AS NOME_ANIMAL,
        IF(
          ISNULL(tab_socios.cotas_socio_01),
          '0.00',
          tab_socios.cotas_socio_01
        ) AS COTAS_ANIMAL,
        tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
        tab_pai_animal.nome AS PAI_ANIMAL,
        tab_mae_animal.nome AS MAE_ANIMAL,
        tab_animais.registro_associacao AS REGISTRO_ANIMAL,
        CONCAT(
          tab_pessoas.nome_razao_social,
          '
            Permanência: ',
          DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
          IF(
            tab_socios.adm_03 = '3',
            '
            Sócio Administrador',
            ''
          )
        ) AS NOME_SOCIO,
        IF(
          ISNULL(tab_socios.cotas_socio_03),
          '0.00',
          tab_socios.cotas_socio_03
        ) AS COTAS_SOCIO,
        tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
        tab_cidades.nome_cidade AS CIDADE_SOCIO,
        tab_estados.sigla_estado AS ESTADO_SOCIO,
        CONCAT(
          tab_pessoas.telefone_celular,
          ' ',
          tab_pessoas.telefone_fixo
        ) AS TELEFONE_SOCIO,
        tab_pessoas.email_usuario AS EMAIL_SOCIO,
        tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
        tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
      FROM
        tab_animais
        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
        JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
        JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
        LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
        LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_03
        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
      WHERE
        tab_animais.id_tipo_animal = '10'
        AND tab_animais.id_grupo = '1'
        AND tab_animais.id_situacao = '1'
        AND (
          tab_animais.nome LIKE '%%'
          OR tab_animais.marca LIKE '%%'
          OR tab_animais.registro_associacao LIKE '%%'
          OR tab_grupo_animais.descricao LIKE '%%'
          OR tab_pai_animal.nome LIKE '%%'
          OR tab_mae_animal.nome LIKE '%%'
          OR tab_animais.chip LIKE '%%'
          OR tab_socios.informacoes_diversas LIKE '%%'
          OR tab_animais.informacoes_diversas LIKE '%%'
        )
        AND tab_animais.id_usuario_sistema = '8301'
      GROUP BY
        tab_animais.id_animal
      ORDER BY
        tab_animais.nome ASC
    )
    UNION
    (
      SELECT
        tab_animais.id_animal AS ID_ANIMAL,
        tab_grupo_animais.descricao AS GRUPO_ANIMAL,
        tab_animais.nome AS NOME_ANIMAL,
        IF(
          ISNULL(tab_socios.cotas_socio_01),
          '0.00',
          tab_socios.cotas_socio_01
        ) AS COTAS_ANIMAL,
        tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
        tab_pai_animal.nome AS PAI_ANIMAL,
        tab_mae_animal.nome AS MAE_ANIMAL,
        tab_animais.registro_associacao AS REGISTRO_ANIMAL,
        CONCAT(
          tab_pessoas.nome_razao_social,
          '
            Permanência: ',
          DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
          IF(
            tab_socios.adm_04 = '3',
            '
            Sócio Administrador',
            ''
          )
        ) AS NOME_SOCIO,
        IF(
          ISNULL(tab_socios.cotas_socio_04),
          '0.00',
          tab_socios.cotas_socio_04
        ) AS COTAS_SOCIO,
        tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
        tab_cidades.nome_cidade AS CIDADE_SOCIO,
        tab_estados.sigla_estado AS ESTADO_SOCIO,
        CONCAT(
          tab_pessoas.telefone_celular,
          ' ',
          tab_pessoas.telefone_fixo
        ) AS TELEFONE_SOCIO,
        tab_pessoas.email_usuario AS EMAIL_SOCIO,
        tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
        tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
      FROM
        tab_animais
        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
        JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
        JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
        LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
        LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_04
        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
      WHERE
        tab_animais.id_tipo_animal = '10'
        AND tab_animais.id_grupo = '1'
        AND tab_animais.id_situacao = '1'
        AND (
          tab_animais.nome LIKE '%%'
          OR tab_animais.marca LIKE '%%'
          OR tab_animais.registro_associacao LIKE '%%'
          OR tab_grupo_animais.descricao LIKE '%%'
          OR tab_pai_animal.nome LIKE '%%'
          OR tab_mae_animal.nome LIKE '%%'
          OR tab_animais.chip LIKE '%%'
          OR tab_socios.informacoes_diversas LIKE '%%'
          OR tab_animais.informacoes_diversas LIKE '%%'
        )
        AND tab_animais.id_usuario_sistema = '8301'
      GROUP BY
        tab_animais.id_animal
      ORDER BY
        tab_animais.nome ASC
    )
    UNION
    (
      SELECT
        tab_animais.id_animal AS ID_ANIMAL,
        tab_grupo_animais.descricao AS GRUPO_ANIMAL,
        tab_animais.nome AS NOME_ANIMAL,
        IF(
          ISNULL(tab_socios.cotas_socio_01),
          '0.00',
          tab_socios.cotas_socio_01
        ) AS COTAS_ANIMAL,
        tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
        tab_pai_animal.nome AS PAI_ANIMAL,
        tab_mae_animal.nome AS MAE_ANIMAL,
        tab_animais.registro_associacao AS REGISTRO_ANIMAL,
        CONCAT(
          tab_pessoas.nome_razao_social,
          '
            Permanência: ',
          DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
          IF(
            tab_socios.adm_05 = '3',
            '
            Sócio Administrador',
            ''
          )
        ) AS NOME_SOCIO,
        IF(
          ISNULL(tab_socios.cotas_socio_05),
          '0.00',
          tab_socios.cotas_socio_05
        ) AS COTAS_SOCIO,
        tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
        tab_cidades.nome_cidade AS CIDADE_SOCIO,
        tab_estados.sigla_estado AS ESTADO_SOCIO,
        CONCAT(
          tab_pessoas.telefone_celular,
          ' ',
          tab_pessoas.telefone_fixo
        ) AS TELEFONE_SOCIO,
        tab_pessoas.email_usuario AS EMAIL_SOCIO,
        tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
        tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
      FROM
        tab_animais
        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
        JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
        JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
        LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
        LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_05
        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
      WHERE
        tab_animais.id_tipo_animal = '10'
        AND tab_animais.id_grupo = '1'
        AND tab_animais.id_situacao = '1'
        AND (
          tab_animais.nome LIKE '%%'
          OR tab_animais.marca LIKE '%%'
          OR tab_animais.registro_associacao LIKE '%%'
          OR tab_grupo_animais.descricao LIKE '%%'
          OR tab_pai_animal.nome LIKE '%%'
          OR tab_mae_animal.nome LIKE '%%'
          OR tab_animais.chip LIKE '%%'
          OR tab_socios.informacoes_diversas LIKE '%%'
          OR tab_animais.informacoes_diversas LIKE '%%'
        )
        AND tab_animais.id_usuario_sistema = '8301'
      GROUP BY
        tab_animais.id_animal
      ORDER BY
        tab_animais.nome ASC
    )
    UNION
    (
      SELECT
        tab_animais.id_animal AS ID_ANIMAL,
        tab_grupo_animais.descricao AS GRUPO_ANIMAL,
        tab_animais.nome AS NOME_ANIMAL,
        IF(
          ISNULL(tab_socios.cotas_socio_01),
          '0.00',
          tab_socios.cotas_socio_01
        ) AS COTAS_ANIMAL,
        tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
        tab_pai_animal.nome AS PAI_ANIMAL,
        tab_mae_animal.nome AS MAE_ANIMAL,
        tab_animais.registro_associacao AS REGISTRO_ANIMAL,
        CONCAT(
          tab_pessoas.nome_razao_social,
          '
            Permanência: ',
          DATE_FORMAT(tab_socios.permanencia_socio_06, '%d/%m/%Y'),
          IF(
            tab_socios.adm_01 = '3',
            '
            Sócio Administrador',
            ''
          )
        ) AS NOME_SOCIO,
        IF(
          ISNULL(tab_socios.cotas_socio_06),
          '0.00',
          tab_socios.cotas_socio_06
        ) AS COTAS_SOCIO,
        tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
        tab_cidades.nome_cidade AS CIDADE_SOCIO,
        tab_estados.sigla_estado AS ESTADO_SOCIO,
        CONCAT(
          tab_pessoas.telefone_celular,
          ' ',
          tab_pessoas.telefone_fixo
        ) AS TELEFONE_SOCIO,
        tab_pessoas.email_usuario AS EMAIL_SOCIO,
        tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
        tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
      FROM
        tab_animais
        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
        JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
        JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
        LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
        LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_06
        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
      WHERE
        tab_animais.id_tipo_animal = '10'
        AND tab_animais.id_grupo = '1'
        AND tab_animais.id_situacao = '1'
        AND (
          tab_animais.nome LIKE '%%'
          OR tab_animais.marca LIKE '%%'
          OR tab_animais.registro_associacao LIKE '%%'
          OR tab_grupo_animais.descricao LIKE '%%'
          OR tab_pai_animal.nome LIKE '%%'
          OR tab_mae_animal.nome LIKE '%%'
          OR tab_animais.chip LIKE '%%'
          OR tab_socios.informacoes_diversas LIKE '%%'
          OR tab_animais.informacoes_diversas LIKE '%%'
        )
        AND tab_animais.id_usuario_sistema = '8301'
      GROUP BY
        tab_animais.id_animal
      ORDER BY
        tab_animais.nome ASC
    )
    UNION
    (
      SELECT
        tab_animais.id_animal AS ID_ANIMAL,
        tab_grupo_animais.descricao AS GRUPO_ANIMAL,
        tab_animais.nome AS NOME_ANIMAL,
        IF(
          ISNULL(tab_socios.cotas_socio_01),
          '0.00',
          tab_socios.cotas_socio_01
        ) AS COTAS_ANIMAL,
        tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
        tab_pai_animal.nome AS PAI_ANIMAL,
        tab_mae_animal.nome AS MAE_ANIMAL,
        tab_animais.registro_associacao AS REGISTRO_ANIMAL,
        CONCAT(
          tab_pessoas.nome_razao_social,
          '
            Permanência: ',
          DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
          IF(
            tab_socios.adm_07 = '3',
            '
            Sócio Administrador',
            ''
          )
        ) AS NOME_SOCIO,
        IF(
          ISNULL(tab_socios.cotas_socio_07),
          '0.00',
          tab_socios.cotas_socio_07
        ) AS COTAS_SOCIO,
        tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
        tab_cidades.nome_cidade AS CIDADE_SOCIO,
        tab_estados.sigla_estado AS ESTADO_SOCIO,
        CONCAT(
          tab_pessoas.telefone_celular,
          ' ',
          tab_pessoas.telefone_fixo
        ) AS TELEFONE_SOCIO,
        tab_pessoas.email_usuario AS EMAIL_SOCIO,
        tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
        tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
      FROM
        tab_animais
        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
        JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
        JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
        LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
        LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_07
        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
      WHERE
        tab_animais.id_tipo_animal = '10'
        AND tab_animais.id_grupo = '1'
        AND tab_animais.id_situacao = '1'
        AND (
          tab_animais.nome LIKE '%%'
          OR tab_animais.marca LIKE '%%'
          OR tab_animais.registro_associacao LIKE '%%'
          OR tab_grupo_animais.descricao LIKE '%%'
          OR tab_pai_animal.nome LIKE '%%'
          OR tab_mae_animal.nome LIKE '%%'
          OR tab_animais.chip LIKE '%%'
          OR tab_socios.informacoes_diversas LIKE '%%'
          OR tab_animais.informacoes_diversas LIKE '%%'
        )
        AND tab_animais.id_usuario_sistema = '8301'
      GROUP BY
        tab_animais.id_animal
      ORDER BY
        tab_animais.nome ASC
    )
    UNION
    (
      SELECT
        tab_animais.id_animal AS ID_ANIMAL,
        tab_grupo_animais.descricao AS GRUPO_ANIMAL,
        tab_animais.nome AS NOME_ANIMAL,
        IF(
          ISNULL(tab_socios.cotas_socio_01),
          '0.00',
          tab_socios.cotas_socio_01
        ) AS COTAS_ANIMAL,
        tab_animais.data_nascimento AS NASCIMENTO_ANIMAL,
        tab_pai_animal.nome AS PAI_ANIMAL,
        tab_mae_animal.nome AS MAE_ANIMAL,
        tab_animais.registro_associacao AS REGISTRO_ANIMAL,
        CONCAT(
          tab_pessoas.nome_razao_social,
          '
            Permanência: ',
          DATE_FORMAT(tab_socios.permanencia_socio_01, '%d/%m/%Y'),
          IF(
            tab_socios.adm_08 = '3',
            '
            Sócio Administrador',
            ''
          )
        ) AS NOME_SOCIO,
        IF(
          ISNULL(tab_socios.cotas_socio_08),
          '0.00',
          tab_socios.cotas_socio_08
        ) AS COTAS_SOCIO,
        tab_pessoas.nome_propriedade_fazenda AS FAZENDA_SOCIO,
        tab_cidades.nome_cidade AS CIDADE_SOCIO,
        tab_estados.sigla_estado AS ESTADO_SOCIO,
        CONCAT(
          tab_pessoas.telefone_celular,
          ' ',
          tab_pessoas.telefone_fixo
        ) AS TELEFONE_SOCIO,
        tab_pessoas.email_usuario AS EMAIL_SOCIO,
        tab_situacoes.descricao AS DESCRICAO_SITUACAO_ANIMAL,
        tab_socios.informacoes_diversas AS INFORMACOES_ANIMAL
      FROM
        tab_animais
        JOIN tab_situacoes ON tab_situacoes.id_situacao = tab_animais.id_situacao
        JOIN tab_sexos ON tab_sexos.id_sexo = tab_animais.id_sexo
        JOIN tab_grupo_animais ON tab_grupo_animais.id_grupo_animal = tab_animais.id_grupo
        LEFT JOIN tab_animais AS tab_pai_animal ON tab_pai_animal.id_animal = tab_animais.id_pai
        LEFT JOIN tab_animais AS tab_mae_animal ON tab_mae_animal.id_animal = tab_animais.id_mae
        JOIN tab_socios ON tab_socios.id_animal = tab_animais.id_animal
        JOIN tab_pessoas ON tab_pessoas.id_pessoa = tab_socios.id_socio_08
        LEFT JOIN tab_cidades ON tab_cidades.id_cidade = tab_pessoas.id_cidade
        LEFT JOIN tab_estados ON tab_estados.id_estado = tab_cidades.id_uf
      WHERE
        tab_animais.id_tipo_animal = '10'
        AND tab_animais.id_grupo = '1'
        AND tab_animais.id_situacao = '1'
        AND (
          tab_animais.nome LIKE '%%'
          OR tab_animais.marca LIKE '%%'
          OR tab_animais.registro_associacao LIKE '%%'
          OR tab_grupo_animais.descricao LIKE '%%'
          OR tab_pai_animal.nome LIKE '%%'
          OR tab_mae_animal.nome LIKE '%%'
          OR tab_animais.chip LIKE '%%'
          OR tab_socios.informacoes_diversas LIKE '%%'
          OR tab_animais.informacoes_diversas LIKE '%%'
        )
        AND tab_animais.id_usuario_sistema = '8301'
      GROUP BY
        tab_animais.id_animal
      ORDER BY
        tab_animais.nome ASC
    )
    ORDER BY
      NOME_ANIMAL ASC,
      NOME_SOCIO ASC
  ) AS tab_socios_animais
HAVING
  COTAS_ANIMAL < 100
  AND COTAS_ANIMAL > 0