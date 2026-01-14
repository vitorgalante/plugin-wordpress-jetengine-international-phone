# JetFormBuilder International Phone Field

Plugin WordPress que adiciona um campo de telefone internacional com validação ao JetFormBuilder, utilizando a biblioteca [intl-tel-input](https://intl-tel-input.com/).

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-orange.svg)

## ✨ Funcionalidades

- 🌍 **Suporte a 200+ países** com bandeiras e códigos
- ✅ **Validação em tempo real** de números de telefone
- 📱 **Responsivo** e otimizado para mobile
- 🔧 **Configurável** via interface gráfica
- 🎨 **Personalizável** com CSS
- 🔌 **Integração nativa** com JetFormBuilder
- 📝 **Múltiplos formatos** de salvamento (E.164, Internacional, Nacional, RFC3966)

## 📋 Requisitos

- WordPress 5.8 ou superior
- PHP 7.4 ou superior
- JetFormBuilder instalado e ativado

## 🚀 Instalação

### Via Upload

1. Baixe o arquivo ZIP do plugin
2. No WordPress, vá em **Plugins > Adicionar Novo > Enviar Plugin**
3. Selecione o arquivo ZIP e clique em **Instalar Agora**
4. Ative o plugin

### Via FTP

1. Extraia o conteúdo do ZIP
2. Faça upload da pasta `jet-form-intl-tel` para `/wp-content/plugins/`
3. Ative o plugin no painel do WordPress

## ⚙️ Configuração

### Configurações Globais

Acesse **Configurações > JFB Telefone Intl** para definir:

| Opção | Descrição | Padrão |
|-------|-----------|--------|
| País Inicial | Código ISO do país selecionado por padrão | `br` |
| Países Preferidos | Lista de países no topo do dropdown | `br,us,pt` |
| Formato de Salvamento | Como o número será salvo no banco | E.164 |
| Separar Código | Exibe o código do país separado | Sim |
| Permitir Seleção | Permite trocar o país no dropdown | Sim |

### Configurações por Campo

Cada campo pode ter configurações individuais no editor Gutenberg:

- País inicial específico
- Lista de países permitidos
- Lista de países excluídos
- Formato de salvamento
- Exibição de validação

## 📖 Como Usar

### No Editor JetFormBuilder

1. Adicione um novo bloco no formulário
2. Procure por **"Telefone Internacional"**
3. Configure as opções no painel lateral
4. Salve o formulário

### Exemplo de Configuração

```
Campo: Telefone de Contato
Nome: telefone_contato
País Inicial: br
Países Preferidos: br, us, pt, ar
Formato: E.164
Obrigatório: Sim
```

## 🎨 Personalização CSS

### Classes Disponíveis

```css
/* Container do campo */
.intl-tel-field-wrapper { }

/* Input do telefone */
input.intl-tel-field { }

/* Estado de erro */
input.intl-tel-field.error { }

/* Estado válido */
input.intl-tel-field.valid { }

/* Mensagem de validação */
.intl-tel-validation-message { }
.intl-tel-validation-message.error { }
.intl-tel-validation-message.success { }

/* Dropdown de países */
.iti__country-list { }
.iti__country { }
```

### Exemplo de Customização

```css
/* Tema escuro */
.intl-tel-field-wrapper .iti__country-list {
    background: #2d2d2d;
    border-color: #444;
}

.intl-tel-field-wrapper .iti__country:hover {
    background: #3d3d3d;
}

/* Bordas arredondadas */
input.intl-tel-field {
    border-radius: 8px;
}
```

## 🔧 API JavaScript

O plugin expõe uma API para uso programático:

```javascript
// Inicializar campo manualmente
JFBIntlTel.init(inputElement);

// Validar número
const isValid = JFBIntlTel.validate(inputElement);

// Obter número formatado
const number = JFBIntlTel.getNumber(inputElement, 'E164');

// Definir país
JFBIntlTel.setCountry(inputElement, 'us');

// Definir número
JFBIntlTel.setNumber(inputElement, '+5511999999999');

// Obter instância
const instance = JFBIntlTel.getInstance(inputElement);
```

## 📊 Formatos de Salvamento

| Formato | Exemplo | Uso Recomendado |
|---------|---------|-----------------|
| E.164 | `+5511999999999` | APIs, integrações, banco de dados |
| Internacional | `+55 11 99999-9999` | Exibição para usuários |
| Nacional | `(11) 99999-9999` | Exibição local |
| RFC3966 | `tel:+55-11-99999-9999` | Links `tel:` |

## 🔍 Hooks Disponíveis

### PHP

```php
// Filtrar configurações globais
add_filter('jfb_intl_tel_settings', function($settings) {
    $settings['initial_country'] = 'us';
    return $settings;
});

// Validação customizada
add_filter('jfb_intl_tel_validate', function($is_valid, $number, $country) {
    // Sua lógica de validação
    return $is_valid;
}, 10, 3);
```

### JavaScript

```javascript
// Evento: campo inicializado
document.addEventListener('jfb-intl-tel-init', function(e) {
    console.log('Campo inicializado:', e.detail.input);
});

// Evento: número validado
document.addEventListener('jfb-intl-tel-validated', function(e) {
    console.log('Válido:', e.detail.isValid);
    console.log('Número:', e.detail.number);
});
```

## ❓ FAQ

### O campo não aparece no editor

Certifique-se de que o JetFormBuilder está instalado e ativado. O bloco aparece na categoria "JetFormBuilder Fields".

### A validação não funciona

Verifique se o arquivo `utils.js` está sendo carregado corretamente (veja o console do navegador). Este arquivo é necessário para validação.

### Como limitar para apenas alguns países?

Use a opção "Apenas Países" no painel de configurações do campo. Por exemplo: `br,us,ar` para mostrar apenas Brasil, EUA e Argentina.

### Como usar com formulários dinâmicos?

O plugin utiliza MutationObserver para detectar campos adicionados dinamicamente. Campos novos são inicializados automaticamente.

## 🐛 Solução de Problemas

1. **Limpe o cache** do WordPress e do navegador
2. **Verifique o console** (F12) para erros JavaScript
3. **Confirme** que não há conflitos com outros plugins
4. **Teste** em um tema padrão (Twenty Twenty-Four)

## 📄 Changelog

Veja [CHANGELOG.md](CHANGELOG.md) para histórico de versões.

## 📜 Licença

Este plugin é licenciado sob GPL v2 ou posterior.

## 👨‍💻 Autor

Desenvolvido por **Vitoor**

## 🙏 Créditos

- [intl-tel-input](https://intl-tel-input.com/) - Biblioteca de telefone internacional
- [JetFormBuilder](https://jetformbuilder.com/) - Plugin de formulários
- [Crocoblock](https://crocoblock.com/) - Equipe por trás do JetFormBuilder
