/**
 * sportmatch.js — Validação e UX compartilhados
 */
(() => {
  'use strict';

  // ── Utilitários de estado ─────────────────────────────────────────
  window.SM = {
    setError(input, id, msg) {
      input.classList.add('invalid');
      input.classList.remove('valid');
      const el = document.getElementById(id);
      if (el) el.textContent = msg;
    },
    setValid(input, id) {
      input.classList.remove('invalid');
      input.classList.add('valid');
      const el = document.getElementById(id);
      if (el) el.textContent = '';
    },
    clearState(input, id) {
      input.classList.remove('invalid', 'valid');
      const el = document.getElementById(id);
      if (el) el.textContent = '';
    },
    // Força de senha
    calcStrength(pw) {
      let s = 0;
      if (pw.length >= 8)          s++;
      if (pw.length >= 12)         s++;
      if (/[A-Z]/.test(pw))        s++;
      if (/[0-9]/.test(pw))        s++;
      if (/[^A-Za-z0-9]/.test(pw)) s++;
      return s;
    },
    updateStrength(fillId, labelId, score) {
      const map = [
        { w: '0%',   bg: 'transparent', label: '' },
        { w: '20%',  bg: '#D9363E',     label: 'Muito fraca' },
        { w: '40%',  bg: '#E07B39',     label: 'Fraca' },
        { w: '60%',  bg: '#D4A017',     label: 'Razoável' },
        { w: '80%',  bg: '#5E9C4A',     label: 'Forte' },
        { w: '100%', bg: '#2E7D52',     label: 'Muito forte' },
      ];
      const s = map[score] || map[0];
      const fill  = document.getElementById(fillId);
      const label = document.getElementById(labelId);
      if (fill)  { fill.style.width = s.w; fill.style.background = s.bg; }
      if (label) label.textContent = s.label;
    },
    // Adiciona spinner e bloqueia botão no submit
    bindSubmit(formId) {
      const form = document.getElementById(formId);
      if (!form) return;
      form.addEventListener('submit', () => {
        const btn = form.querySelector('.btn-primary');
        if (btn) { btn.classList.add('loading'); btn.disabled = true; }
      });
    },
    // Valida campos obrigatórios genéricos antes do submit
    validateRequired(fields) {
      let ok = true;
      fields.forEach(({ el, id, msg }) => {
        if (!el) return;
        if (!el.value.trim()) { SM.setError(el, id, msg || 'Campo obrigatório.'); ok = false; }
        else SM.setValid(el, id);
      });
      return ok;
    }
  };

  // ── Inicialização automática de formulários com data-sm-form ──────
  document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-sm-form]').forEach(form => {
      form.addEventListener('submit', (e) => {
        let valid = true;
        form.querySelectorAll('[required]').forEach(el => {
          if (!el.value.trim()) {
            const errId = 'err-' + el.id;
            SM.setError(el, errId, 'Campo obrigatório.');
            valid = false;
          }
        });
        if (!valid) {
          e.preventDefault();
          const first = form.querySelector('.invalid');
          if (first) first.focus();
        } else {
          const btn = form.querySelector('.btn-primary');
          if (btn) { btn.classList.add('loading'); btn.disabled = true; }
        }
      });

      // Limpa erro ao digitar
      form.querySelectorAll('.field-input, .field-select, .field-textarea').forEach(el => {
        el.addEventListener('input', () => {
          if (el.value.trim()) SM.setValid(el, 'err-' + el.id);
        });
        el.addEventListener('blur', () => {
          if (el.hasAttribute('required') && !el.value.trim())
            SM.setError(el, 'err-' + el.id, 'Campo obrigatório.');
        });
      });
    });

    // Senha + confirmar
    const senha    = document.getElementById('senha');
    const confirma = document.getElementById('confirmar');
    if (senha) {
      senha.addEventListener('input', () => {
        SM.updateStrength('strengthFill', 'strengthLabel', SM.calcStrength(senha.value));
        if (confirma?.value) checkConfirma();
      });
    }
    function checkConfirma() {
      if (!confirma || !senha) return;
      if (confirma.value !== senha.value)
        SM.setError(confirma, 'err-confirmar', 'As senhas não coincidem.');
      else
        SM.setValid(confirma, 'err-confirmar');
    }
    if (confirma) {
      confirma.addEventListener('input', checkConfirma);
      confirma.addEventListener('blur', checkConfirma);
    }
  });
})();
