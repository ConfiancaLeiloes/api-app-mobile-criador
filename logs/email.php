<?php


echo $mensagem = '<html>
    <body style="width: 800px; font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif !important; font-weight: normal;  color: #545454">
        
        <div style="margin-top: 30px;"><img src="https://confiancacriador.digital/assets/img/logo.png" height="80" /></div>

        <div style="margin-top: 30px;">
          <h1 style="color: #EC6608; margin-bottom: 0"> Olá, '.$nome_usuario . '! </h1>
          <h3 style="margin-top: 0;">Seja muito bem-vindo ao Confiança Criador, <br> a plataforma mais completa para a gestão de seu haras.</h3>
          <p class="margin-top: 30px;">Você se cadastrou em nosso site e forneceu as seguintes informações:</p>
        </div>

        <div style="width:100%; margin-top: 30px;">
          <h4> 1 - Plano escolhido:</h4>
          <p style="margin: 3px;">Plano:   <b style="color: #EC6608">'. $texto_pacote.'</b></p>
          <p style="margin: 3px;">Período:  <b>'. $texto_periodo.'</b></p>
          <p style="margin: 3px;">Valor:   <b>'. $valor.'</b></p>
          <p style="margin: 3px;">Contratação: <b>'. date("d/m/Y") .'</b></p>
          <p style="margin: 3px;">Link de pagamento:   <b>'. $link_pagamento .'</b></p>
        </div>

        <div style="width:100%; margin-top: 30px;">
          <h4>2 - Dados cadastrais:</h4>
          <p style="margin: 3px;">Nome:   <b style="color: #EC6608">'. $nome_usuario.'</b></p>
          <p style="margin: 3px;">Fazenda/Haras:  <b>'. $nome_propriedade.'</b></p>
          <p style="margin: 3px;">Plantel:  <b>'. $total_animais_plantel .' </b></p>
          <p style="margin: 3px;">Cidade / UF:   <b>'.$cidade_uf->nome_cidade.' / '.$cidade_uf->sigla_estado.'</b></p>
          <p style="margin: 3px;">Celular: <b>'. $celular_usuario.'</b></p>
        </div>

        <div style="width:100%; margin-top: 30px;">
          <h4> 3 - Dados de Acesso ao Sistema e Aplicativo:</h4>
          <p style="margin: 3px;">E-mail: <b style="color: #EC6608">'. $email_usuario.'</b></p>
          <p style="margin: 3px;">Senha: <b style="color: #EC6608">'. $senha_usuario.'</b></p>
        </div>

        <div style="width:100%; margin-top: 30px;">
          <h4>4 – Download do Confiança Criador Office:</h4>
          <a href="https://confiancacriador.digital/download/Setup_Confianca_Criador.exe" target="_blank"><img src="https://confiancacriador.digital/assets/img/email/download.svg" /></a>

          <h4>5 – Download do Aplicativo Confiança Criador nas lojas:</h4>
          <a href="#">
             <img src="https://confiancacriador.digital/assets/img/email/apple.svg" />
          </a>
          <a href="#">
            <img src="https://confiancacriador.digital/assets/img/email/android.svg" />
          </a>
        </div>
 
        <div style="width:100%; margin-top: 30px;">
            <h4>6 – Nossos Termos de Uso e Políticas de Privacidade:</h4>
            <a  href="https://confiancacriador.digital/termos/termos_de_uso.pdf" target="_blank" style="text-decoration:none;">
              <span style="width: 100px; height: 30px; padding: 10px 30px; border-radius: 10px; text-align:center; background-color: #1A1A1A; color: #ffffff;"> Baixar / Visualizar </span>
            </a>
        </div>

        <div style="width:100%;margin-top: 30px;">
          <p>Nossa equipe está à sua inteira disposição caso necessite de ajuda!</p>
          <h4 style="margin-bottom: 0 !important;">Suporte:</h4>
          <p style="margin-top: 0 !important; margin-bottom: 0 !important;">WhatsApp: <b>(31) 2118-1776 </b>– E-mail: <b>suporte@confiancacriador.digital</b></p>
          <p style="margin-top: 0 !important;">Site: <a href="https://confiancacriador.digital" target="_blank"></a>www.confiancacriador.digital</p>
        </div>

    </body>
</html>';


$mensagem_confianca = '<html>
    <body style="width: 800px; font-family:"Segoe UI", Tahoma, Geneva, Verdana, sans-serif !important; font-weight: normal;  color: #545454">
        
        <div style="margin-top: 30px;"><img src="https://confiancacriador.digital/assets/img/logo.png" height="80" /></div>

        <div style="margin-top: 30px;">
          <h1 style="color: #EC6608; margin-bottom: 0"> Olá Confiança! </h1>
          <h3 style="margin-top: 0;">Recebemos um novo cadastro no Confiança Criador.</h3>
          <p class="margin-top: 30px;">O cliente: '. $nome_usuario . ' se cadastrou em nosso site e forneceu as seguintes informações:</p>
        </div>

        <div style="width:100%; margin-top: 30px;">
          <h4> 1 - Plano escolhido:</h4>
          <p style="margin: 3px;">Plano:   <b style="color: #EC6608">'. $texto_pacote.'</b></p>
          <p style="margin: 3px;">Período:  <b>'. $texto_periodo.'</b></p>
          <p style="margin: 3px;">Valor:   <b>'. $valor.'</b></p>
          <p style="margin: 3px;">Contratação: <b>'. date("d/m/Y") .'</b></p>
        </div>

        <div style="width:100%; margin-top: 30px;">
          <h4>2 - Dados cadastrais:</h4>
          <p style="margin: 3px;">Nome:   <b style="color: #EC6608">'. $nome_usuario.'</b></p>
          <p style="margin: 3px;">Fazenda/Haras:  <b>'. $nome_propriedade.'</b></p>
          <p style="margin: 3px;">Plantel:  <b>'. $total_animais_plantel .' </b></p>
          <p style="margin: 3px;">Cidade / UF:   <b>'.$cidade_uf->nome_cidade.' / '.$cidade_uf->sigla_estado.'</b></p>
          <p style="margin: 3px;">Celular: <b>'. $celular_usuario.'</b></p>
        </div>

        <div style="width:100%; margin-top: 30px;">
          <h4> 3 - Dados de Acesso ao Sistema e Aplicativo:</h4>
          <p style="margin: 3px;">E-mail: <b style="color: #EC6608">'. $email_usuario.'</b></p>
        </div>

    </body>
</html>';