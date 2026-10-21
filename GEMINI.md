# Xhybrid Multi-Project Workspace Rules & Context

Este ambiente faz parte de um ecossistema multi-repositório composto por 4 projetos interdependentes localizados em `C:\Users\Bosco\Documents\GitHub\`:

1. **`crm_software`** (`C:\Users\Bosco\Documents\GitHub\crm_software`)
   - **Função**: Sistema de captura de leads, CRM administrativo, banco SQLite (`data/`) e APIs.
   - **Stack**: PHP 8+, SQLite (`pdo_sqlite`), HTML/JS/CSS.

2. **`xhybrid_qa`** (`C:\Users\Bosco\Documents\GitHub\xhybrid_qa`)
   - **Função**: Suíte de testes de qualidade (QA), testes automatizados E2E e de integração, logs de execução.
   - **Stack**: Playwright, PHP.

3. **`xhybrid_wa_bot`** (`C:\Users\Bosco\Documents\GitHub\xhybrid_wa_bot`)
   - **Função**: Robô de automação para WhatsApp e envio de mensagens/notificações.
   - **Stack**: Node.js, Playwright.

4. **`xhybrid_site`** (`C:\Users\Bosco\Documents\GitHub\xhybrid_site`)
   - **Função**: Site principal da Xhybrid (landing page, checkout, páginas institucionais e router PHP).
   - **Stack**: HTML5, CSS3, JavaScript, PHP.

---

### Diretrizes de Ação e Edição pelo Agente de IA

- **Acesso e Modificação Direta**: O assistente tem permissão explícita para ler, criar, editar e executar testes em qualquer um dos 4 projetos.
- **Resolução de Contexto**: Sempre que o usuário mencionar um projeto ou módulo específico (ex.: *"altere o crm_software"*, *"mude o bot do whatsapp"*, *"ajuste o teste no qa"*, *"mude a landing page no site"*), o assistente deve direcionar as alterações diretamente para os arquivos da respectiva pasta.
- **Consistência Cross-Project**: Quando houver alterações de contratos, rotas de API, parâmetros ou bancos de dados que afetem mais de um projeto (ex.: site enviando lead para o crm, ou crm disparando bot), garanta que ambos os lados sejam atualizados de forma consistente.
- **Arquivo de Workspace**: O workspace unificado está salvo em:
  - `C:\Users\Bosco\Documents\GitHub\xhybrid.code-workspace`
  - `C:\Users\Bosco\Documents\GitHub\xhybrid_site\xhybrid.code-workspace`
