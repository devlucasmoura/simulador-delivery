# 🍕 Simulador de Sistema de Delivery

Sistema de simulação de pedidos para aplicativo de entrega de comida.

## 🚀 Como usar

### 1. Configurar o Banco de Dados

**Passo a passo para executar o `banco_dados.sql` no phpMyAdmin:**

1. **Abra o phpMyAdmin:**
   - Inicie o XAMPP/WAMP
   - Acesse: `http://localhost/phpmyadmin`

2. **Execute o script SQL:**
   - Clique na aba **"SQL"** no topo
   - **Abra o arquivo `banco_dados.sql` no Bloco de Notas**
   - **Copie TODO o conteúdo** do arquivo
   - **Cole no phpMyAdmin** na área de texto SQL
   - Clique no botão **"Executar"**

3. **Verifique se deu certo:**
   - Deve aparecer: "✅ BANCO DE DADOS CRIADO COM SUCESSO!"
   - No menu lateral, aparecerá o banco `app_entrega`
   - Dentro dele, as tabelas: `usuarios`, `produtos`, `pedidos`, `pedido_itens`

### 2. Configurar os Arquivos PHP

1. **Copie os arquivos para o servidor:**
   - `simulador_pedidos.php`
   - `style.css`
   - Coloque na pasta: `C:\xampp\htdocs\projeto-delivery\`

2. **Acesse o sistema:**
   - Abra o navegador
   - Digite: `http://localhost/projeto-delivery/simulador_pedidos.php`

### 3. Usar o Sistema

- **🚀 Iniciar Simulação**: Cria pedidos automáticos
- **📋 Listar Pedidos**: Mostra todos os pedidos
- **🗑️ Limpar Resultados**: Limpa a tela

## 🛠️ Tecnologias
- PHP, MySQL, HTML, CSS

## 📁 Arquivos do Projeto
- `simulador_pedidos.php` - Sistema principal
- `style.css` - Estilos da interface
- `banco_dados.txt` - Script do banco (executar no MySQL)
- `README.md` - Instruções de uso

## 👨‍💻 Autor

Lucas Moura, Patrick, Pedro, Maria
