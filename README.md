# Adsmanager for Joomla

**Adsmanager** je Joomla plugin na správu a zobrazovanie reklám na vašich stránkach. Umožňuje jednoducho pridávať, upravovať a zobrazovať reklamy na rôznych pozíciách šablóny.

---

## Funkcie

- Správa textových a obrazových reklám
- Nastavenie pozície reklamy podľa modulov Joomla
- Podpora vlastného HTML/CSS pre každú reklamu
- Možnosť vloženia reklamy priamo do článkov pomocou {loadposition} alebo {loadmodule}
- Štatistiky zobrazení a kliknutí (pripravené na budúce rozšírenie)
- Kompatibilita s Joomla 4.x

---

## Inštalácia

1. Stiahnite si plugin vo formáte `.zip`.
2. Prejdite do administrácie Joomla: **Rozšírenia > Správca rozšírení > Inštalovať**.
3. Nahrajte súbor `.zip` a kliknite na **Inštalovať**.
4. Aktivujte plugin v **Správca modulov** alebo **Správca pluginov** podľa typu pluginu.

---

## Použitie

### Vytvorenie reklamy

1. Prejdite do **Komponenty > Adsmanager > Pridať reklamu**.
2. Zadajte názov, typ reklamy (text/obrázok), cieľový odkaz a pozíciu.
3. Kliknite na **Uložiť & Zavrieť**.

### Zobrazenie reklamy

- **Ako modul:** Priraďte reklamu k určitej pozícii šablóny.
- **V článku:** Použite `{loadposition adsmanager}` alebo `{loadmodule adsmanager}`.
- **V PHP šablóne:**
```php
<?php
echo JModuleHelper::renderModule(JModuleHelper::getModule('mod_adsmanager', 'ID_REKLAMY'));
?>
