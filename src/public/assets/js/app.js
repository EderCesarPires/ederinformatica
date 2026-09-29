(function () {
    'use strict';

    const brl = (v) => v.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    // ---------- Confirmação de exclusão ----------
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (e) => {
            if (!confirm(form.dataset.confirm)) e.preventDefault();
        });
    });

    // ---------- Máscaras ----------
    const masks = {
        cep(v) {
            v = v.replace(/\D/g, '').slice(0, 8);
            return v.length > 5 ? v.slice(0, 5) + '-' + v.slice(5) : v;
        },
        document(v) {
            v = v.replace(/\D/g, '').slice(0, 14);
            if (v.length <= 11) {
                return v.replace(/(\d{3})(\d)/, '$1.$2')
                        .replace(/(\d{3})(\d)/, '$1.$2')
                        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            }
            return v.replace(/^(\d{2})(\d)/, '$1.$2')
                    .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
                    .replace(/\.(\d{3})(\d)/, '.$1/$2')
                    .replace(/(\d{4})(\d)/, '$1-$2');
        },
    };
    document.querySelectorAll('[data-mask]').forEach((input) => {
        const fn = masks[input.dataset.mask];
        input.addEventListener('input', () => { input.value = fn(input.value); });
    });

    // ---------- Busca de endereço pelo CEP (ViaCEP) ----------
    const cep = document.getElementById('cep');
    const address = document.getElementById('address');
    const hint = document.getElementById('cep-hint');
    if (cep && address) {
        cep.addEventListener('input', async () => {
            const digits = cep.value.replace(/\D/g, '');
            if (digits.length !== 8) return;
            hint.textContent = 'Buscando endereço…';
            hint.classList.remove('error');
            try {
                const res = await fetch(`https://viacep.com.br/ws/${digits}/json/`);
                const data = await res.json();
                if (data.erro) throw new Error();
                address.value = [data.logradouro, data.bairro, `${data.localidade}/${data.uf}`]
                    .filter(Boolean).join(', ');
                hint.textContent = 'Endereço encontrado.';
                document.getElementById('complement')?.focus();
            } catch {
                hint.textContent = 'CEP não encontrado. Preencha o endereço manualmente.';
                hint.classList.add('error');
            }
        });
    }

    // ---------- Formulário de OS: itens dinâmicos e total ao vivo ----------
    const orderForm = document.getElementById('orderForm');
    if (orderForm) {
        const items = document.getElementById('items');
        const discount = document.getElementById('discount_percent');
        const surcharge = document.getElementById('surcharge_percent');
        const template = items.querySelector('.item-row').cloneNode(true);

        const recalc = () => {
            let subtotal = 0;
            items.querySelectorAll('.item-row').forEach((row) => {
                const opt = row.querySelector('.svc').selectedOptions[0];
                const price = parseFloat(opt?.dataset.price || 0);
                const qty = Math.max(1, parseInt(row.querySelector('.qty').value, 10) || 1);
                const line = price * qty;
                row.querySelector('.line-total').textContent = brl(line);
                subtotal += line;
            });
            // Mesma ordem do PriceCalculator: desconto -> acréscimo
            const d = Math.min(100, Math.max(0, parseFloat(discount.value) || 0));
            const s = Math.min(100, Math.max(0, parseFloat(surcharge.value) || 0));
            const afterDiscount = subtotal * (1 - d / 100);
            const surchargeValue = afterDiscount * s / 100;
            document.getElementById('sumSubtotal').textContent = brl(subtotal);
            document.getElementById('sumDiscount').textContent = '- ' + brl(subtotal - afterDiscount);
            document.getElementById('sumSurcharge').textContent = '+ ' + brl(surchargeValue);
            document.getElementById('sumTotal').textContent = brl(Math.max(0, afterDiscount + surchargeValue));
        };

        document.getElementById('addItem').addEventListener('click', () => {
            const row = template.cloneNode(true);
            row.querySelector('.svc').value = '';
            row.querySelector('.qty').value = 1;
            items.appendChild(row);
            row.querySelector('.svc').focus();
            recalc();
        });

        items.addEventListener('click', (e) => {
            const btn = e.target.closest('.remove-item');
            if (!btn) return;
            if (items.querySelectorAll('.item-row').length > 1) {
                btn.closest('.item-row').remove();
            } else {
                btn.closest('.item-row').querySelector('.svc').value = '';
            }
            recalc();
        });

        orderForm.addEventListener('input', recalc);
        orderForm.addEventListener('change', recalc);
        recalc();
    }
})();
