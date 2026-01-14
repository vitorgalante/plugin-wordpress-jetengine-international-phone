# Changelog

Todas as mudanças notáveis neste projeto serão documentadas neste arquivo.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/pt-BR/1.0.0/),
e este projeto adere ao [Versionamento Semântico](https://semver.org/lang/pt-BR/).

## [1.0.1] - 2026-01-12

### Corrigido
- Verificação de dependência agora suporta JetFormBuilder como módulo do JetEngine
- Removido `Requires Plugins` do cabeçalho para evitar bloqueio de ativação

---

## [1.0.0] - 2026-01-12

### Adicionado
- 🎉 Lançamento inicial do plugin
- Campo de telefone internacional para JetFormBuilder
- Integração com biblioteca intl-tel-input v23.0.12
- Validação em tempo real de números de telefone
- Suporte a 200+ países com bandeiras
- Múltiplos formatos de salvamento (E.164, Internacional, Nacional, RFC3966)
- Bloco Gutenberg com preview no editor
- Página de configurações globais no admin
- Configurações individuais por campo
- Suporte a campos dinâmicos via MutationObserver
- CSS responsivo e otimizado para mobile
- API JavaScript para uso programático
- Documentação completa em português
- Suporte a RTL (Right-to-Left)
- Dark mode automático via `prefers-color-scheme`

### Segurança
- Sanitização de todos os inputs
- Validação no servidor (PHP) e cliente (JavaScript)
- Escape de outputs conforme padrões WordPress

## [Unreleased]

### Planejado
- [ ] Detecção automática de país por IP/geolocalização
- [ ] Integração com WhatsApp (click-to-chat)
- [ ] Tema escuro manual (toggle)
- [ ] Analytics de países mais utilizados
- [ ] Testes automatizados (PHPUnit + Jest)
- [ ] Internacionalização completa (i18n)
- [ ] Integração com RD Station
- [ ] Máscara de input customizável
- [ ] Suporte a múltiplos números por campo

---

## Guia de Versionamento

- **MAJOR** (X.0.0): Mudanças incompatíveis com versões anteriores
- **MINOR** (0.X.0): Novas funcionalidades compatíveis
- **PATCH** (0.0.X): Correções de bugs compatíveis

## Links

- [Repositório](https://github.com/vitoor/jet-form-intl-tel)
- [Issues](https://github.com/vitoor/jet-form-intl-tel/issues)
- [Releases](https://github.com/vitoor/jet-form-intl-tel/releases)
