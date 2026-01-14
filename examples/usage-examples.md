# Exemplos de Uso

Este documento contém exemplos práticos de como utilizar o plugin JetFormBuilder International Phone Field.

## Índice

1. [Uso Básico](#uso-básico)
2. [Configurações Avançadas](#configurações-avançadas)
3. [Integração com JavaScript](#integração-com-javascript)
4. [Hooks e Filtros PHP](#hooks-e-filtros-php)
5. [Casos de Uso Comuns](#casos-de-uso-comuns)

---

## Uso Básico

### Formulário de Contato Simples

1. Crie um novo formulário no JetFormBuilder
2. Adicione o bloco "Telefone Internacional"
3. Configure:
   - **Nome:** `telefone`
   - **Label:** `Seu Telefone`
   - **Obrigatório:** Sim
   - **País Inicial:** `br`

O número será salvo no formato E.164 (ex: `+5511999999999`).

---

## Configurações Avançadas

### Apenas Países da América do Sul

```
Apenas Países: br,ar,uy,py,cl,co,pe,ec,ve,bo
```

### Excluir Países Específicos

```
Excluir Países: ru,cn,kp
```

### Formato Nacional para Exibição

Se você precisa exibir o número no formato nacional:

1. Configure **Formato de Salvamento** como `NATIONAL`
2. O número será salvo como `(11) 99999-9999`

> ⚠️ **Nota:** O formato E.164 é recomendado para integrações com APIs.

---

## Integração com JavaScript

### Validar Antes de Enviar via AJAX

```javascript
document.querySelector('#meu-form').addEventListener('submit', function(e) {
    const phoneInput = document.querySelector('input.intl-tel-field');
    
    if (!JFBIntlTel.validate(phoneInput)) {
        e.preventDefault();
        alert('Por favor, insira um número de telefone válido.');
        phoneInput.focus();
        return false;
    }
    
    // Obtém o número formatado
    const phoneNumber = JFBIntlTel.getNumber(phoneInput, 'E164');
    console.log('Número válido:', phoneNumber);
});
```

### Detectar Mudança de País

```javascript
const phoneInput = document.querySelector('input.intl-tel-field');

phoneInput.addEventListener('countrychange', function() {
    const instance = JFBIntlTel.getInstance(phoneInput);
    const countryData = instance.iti.getSelectedCountryData();
    
    console.log('País selecionado:', countryData.name);
    console.log('Código:', countryData.dialCode);
    console.log('ISO:', countryData.iso2);
});
```

### Pré-preencher com Número Existente

```javascript
// Quando a página carregar
document.addEventListener('DOMContentLoaded', function() {
    const phoneInput = document.querySelector('input.intl-tel-field');
    const savedNumber = '+5511999999999'; // Do banco de dados
    
    // Aguarda inicialização
    setTimeout(function() {
        JFBIntlTel.setNumber(phoneInput, savedNumber);
    }, 500);
});
```

### Integração com Google Analytics

```javascript
phoneInput.addEventListener('blur', function() {
    if (JFBIntlTel.validate(phoneInput)) {
        const instance = JFBIntlTel.getInstance(phoneInput);
        const countryData = instance.iti.getSelectedCountryData();
        
        // Envia evento para GA4
        gtag('event', 'phone_entered', {
            'country': countryData.iso2,
            'country_name': countryData.name
        });
    }
});
```

---

## Hooks e Filtros PHP

### Modificar Configurações Globais

```php
// No functions.php do tema

add_filter('jfb_intl_tel_settings', function($settings) {
    // Define país inicial baseado no idioma do site
    $locale = get_locale();
    
    if (strpos($locale, 'pt_BR') !== false) {
        $settings['initial_country'] = 'br';
    } elseif (strpos($locale, 'es_') !== false) {
        $settings['initial_country'] = 'es';
    } else {
        $settings['initial_country'] = 'us';
    }
    
    return $settings;
});
```

### Validação Customizada no Servidor

```php
add_filter('jet-form-builder/validate-field', function($is_valid, $value, $field) {
    // Apenas para campos de telefone
    if ($field['blockName'] !== 'jet-forms/intl-tel-field') {
        return $is_valid;
    }
    
    // Verifica se é número brasileiro
    if (strpos($value, '+55') === 0) {
        // Valida formato brasileiro (11 dígitos após +55)
        $number = preg_replace('/[^0-9]/', '', substr($value, 3));
        
        if (strlen($number) !== 11) {
            return new WP_Error(
                'invalid_br_phone',
                'Número brasileiro deve ter 11 dígitos (DDD + número).'
            );
        }
    }
    
    return $is_valid;
}, 10, 3);
```

### Formatar Número Antes de Salvar

```php
add_filter('jet-form-builder/request/field-value', function($value, $field_name, $request) {
    // Apenas para campo específico
    if ($field_name !== 'telefone_whatsapp') {
        return $value;
    }
    
    // Remove o + para integração com WhatsApp API
    return ltrim($value, '+');
}, 10, 3);
```

### Adicionar Campo ao Email de Notificação

```php
add_filter('jet-form-builder/send-email/content', function($content, $data) {
    if (isset($data['telefone'])) {
        $phone = $data['telefone'];
        
        // Adiciona link clicável
        $phone_link = '<a href="tel:' . esc_attr($phone) . '">' . esc_html($phone) . '</a>';
        $content = str_replace('{telefone}', $phone_link, $content);
    }
    
    return $content;
}, 10, 2);
```

---

## Casos de Uso Comuns

### 1. Formulário de Cadastro com WhatsApp

**Configuração do campo:**
- Nome: `whatsapp`
- Label: `WhatsApp`
- Formato: `E164`
- Descrição: `Número com DDD para contato via WhatsApp`

**Após salvar, gerar link:**
```php
$whatsapp = get_user_meta($user_id, 'whatsapp', true);
$whatsapp_link = 'https://wa.me/' . ltrim($whatsapp, '+');
echo '<a href="' . esc_url($whatsapp_link) . '">Enviar WhatsApp</a>';
```

### 2. Formulário Internacional

**Configuração:**
- Países Preferidos: `br,us,gb,de,fr,es,it,pt`
- Separar Código: Sim
- Formato: `INTERNATIONAL`

### 3. Apenas Brasil

**Configuração:**
- Apenas Países: `br`
- País Inicial: `br`
- Permitir Seleção: Não

### 4. Suporte ao Cliente

**Configuração:**
- Países Preferidos: `br,ar,cl,co,mx`
- Formato: `E164` (para integração com sistemas de call center)
- Validação: Sim

### 5. Lead Generation com UTM

```javascript
// Captura UTMs e envia junto com o telefone
document.querySelector('form').addEventListener('submit', function(e) {
    const phoneInput = document.querySelector('input[name="telefone"]');
    const phone = JFBIntlTel.getNumber(phoneInput, 'E164');
    
    // Adiciona campos hidden com UTMs
    const urlParams = new URLSearchParams(window.location.search);
    ['utm_source', 'utm_medium', 'utm_campaign'].forEach(param => {
        if (urlParams.has(param)) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = param;
            hidden.value = urlParams.get(param);
            this.appendChild(hidden);
        }
    });
});
```

---

## Dicas de Performance

1. **Carregue apenas onde necessário:**
```php
add_action('wp_enqueue_scripts', function() {
    // Remove scripts em páginas sem formulário
    if (!is_page('contato') && !is_singular('produto')) {
        wp_dequeue_script('intl-tel-input');
        wp_dequeue_script('jfb-intl-tel-field');
        wp_dequeue_style('intl-tel-input');
    }
});
```

2. **Cache do utils.js:**
   - A biblioteca utils.js é carregada do CDN
   - Browsers modernos fazem cache automaticamente
   - Para performance máxima, hospede localmente

3. **Lazy load em formulários modais:**
```javascript
// Inicializa apenas quando modal abre
modalButton.addEventListener('click', function() {
    const phoneInput = modal.querySelector('input.intl-tel-field');
    if (!JFBIntlTel.getInstance(phoneInput)) {
        JFBIntlTel.init(phoneInput);
    }
});
```

---

## Troubleshooting

### Dropdown não abre no mobile

Adicione ao CSS:
```css
.iti__country-list {
    z-index: 9999 !important;
}
```

### Conflito com outros plugins de máscara

```javascript
// Desativa máscaras conflitantes
jQuery(document).ready(function($) {
    $('input.intl-tel-field').off('input.mask');
});
```

### Campo não inicializa em formulário AJAX

```javascript
// Reinicializa após AJAX
$(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.url.includes('jet-form-builder')) {
        setTimeout(function() {
            JFBIntlTel.initAll();
        }, 100);
    }
});
```
