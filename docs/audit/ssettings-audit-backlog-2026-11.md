# sSettings: звірка історичного аудиту та backlog за листопад 2026

Дата статичного перегляду: **10.11.2026**, Europe/Kiev. Документ містить задачі для подальшої роботи, без реалізації виправлень і без перевіреної експлуатації.

## Джерело, інструкції та перевірений зріз

- Корінь Git і пакета: `H:/PhpstormProjects/sSettings`; Composer package: `seiger/ssettings` (`composer.json:2`), гілка **`2.x`**, HEAD **`c8b6cfbfcd5ef812bd3d0e5254bd5f50422c8a93`**.
- Початковий `git status --porcelain=v1 --untracked-files=all` порожній: staged, unstaged та untracked змін не було. Наявні файли не змінювалися. Єдиний новий файл цієї роботи — цей backlog.
- Застосовано надані користувачем AGENTS.md-інструкції та прочитано канонічний `H:/PhpstormProjects/MiddleSokil/SokilBook/contracts/WRITING-CONTRACT.md`. Окремих AGENTS.md у пакеті й перевірених батьківських каталогах `H:/` та `H:/PhpstormProjects` не знайдено. Прочитано README, `composer.json`, документацію та відповідний код; тести читалися лише як текст.
- У пакеті є `docs/`, але усталених `docs/audit/` або `docs/security/` із tracked-аудитами не знайдено. Використано запитаний `docs/audit/`. Цільове ім'я перед створенням було вільне; наявний файл не перезаписано.
- Архів аудиту **06.11.2026 не знайдено в перевірених локальних джерелах**: пакет, пошук ID у Markdown/JSON/text MiddleSokil та пошук назв audit-архівів і датованих audit-файлів у дереві `H:/PhpstormProjects`. Це не перевірка всіх дисків. Повні картки архіву не прочитано; історичним джерелом є **витяг у завданні користувача**. Інші package-backlog не використовуються як докази sSettings.
- Прямі історичні картки: **H-048 / HIGH / CONFIRMED** і **L-006 / LOW / CONFIRMED** у зрізі **04.11.2026**. CONFIRMED означав наявність конструкції в тодішньому коді, а не доведену експлуатацію. Оновлення 06.11 змінювало посилання й рядки, а не проводило новий аудит. Історичні рядки — лише орієнтири.
- ROOT-картка **I-006 / NOT_DETERMINED** є контекстом авторизації; її severity у витягу не вказана. Її статус тут не змінюється.
- Виконано лише локальне читання тексту/Git-метаданих і створення Markdown. Застосунок, PHP-код, HTTP-запити, міграції, операції з БД і runtime-тести не запускалися. `git pull/fetch`, commit, push, PR не виконувалися.

### Додаткові локальні джерела

| Позначення | Корінь | Ідентифікація та межі |
| --- | --- | --- |
| `PKG` | `H:/PhpstormProjects/sSettings` | Зазначений вище HEAD; усі шляхи без префікса належать цьому пакету. |
| `CORE` | `H:/PhpstormProjects/evolution` | Гілка `3.6.x`, HEAD `d0f4fc614dff53f957e0c0df85486de06caec3b4`. Є наявний untracked `core/tests/standalone/DatabaseBackupCliTest.php`; його не читали й не змінювали. Використані tracked-файли не мають локальних змін. Прочитано AGENTS.md цього checkout. Це довідкове ядро, а не підтверджена залежність цільового сайту. |
| `UI` | `H:/PhpstormProjects/Extras/evo-ui` | HEAD `c9a4a18651b9ea8d0f0d787871b675b82176c868`; чисте дерево. Окремий довідковий checkout. |
| `DEP` | `H:/PhpstormProjects/Extras/MiddleSokil/evolution-runtime-dependencies-partial` | Частковий локальний набір vendor-файлів. `composer/installed.json` заявляє Livewire `v4.4.8`, source `8d7f8fc6138274a2f3384c9db354ca012215a259`, та illuminate/database `v13.38.0`, source `add3b4602c77c38edbee26be9ab55e7eec621182`. Метадані не доводять незмінність vendor-файлів або їх установлення разом із PKG/CORE/UI. |

`composer.json:13–16` вимагає PHP `^8.4`, Evolution `^3.5.8`, EvoUI `^1.3`. У PKG немає власного vendor/lock, який установлював би фактичну комбінацію залежностей. Контракти CORE/UI/DEP нижче — статичні орієнтири; їх спільну роботу й конфігурацію цільового розгортання не перевірено.

Для повторної звірки DEP зафіксовано SHA256 прочитаних файлів:

| Файл від DEP | SHA256 |
| --- | --- |
| `livewire/livewire/src/Mechanisms/PersistentMiddleware/PersistentMiddleware.php` | `C4804FC92C0E4F67A5AAD779AF5C40A0491E4AA2FC56E2E31AF3C3887D0CDB2D` |
| `livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php` | `1AEC1B8E66EFC27467FECBB6EB2C1A90BEA96CA62F5AC59A0EC2663C87EAAFDF` |
| `livewire/livewire/src/Features/SupportLifecycleHooks/SupportLifecycleHooks.php` | `121E0E6D01400CDBF16BEC47122F1BCBCE884B1460061B0CD79B98141EF6CAE8` |
| `livewire/livewire/src/Mechanisms/HandleRequests/HandleRequests.php` | `9B01CD25DD949F6F5B773620417BC48281206B75762F2A14FB961EB111632F72` |
| `illuminate/database/Query/Grammars/Grammar.php` | `24CF27545734C3FFC0CF123E9D536DD7DDCBAB6C3C37A6A92C048A81461227C2` |

## Карта шляхів і перевірок

1. **Реєстрація.** `src/sSettingsServiceProvider.php:34–36,49–68,72–95` реєструє manager-частину лише за `IN_MANAGER_MODE`. Файловий модуль оголошено через `registerModule(..., ['hidden' => true])`, без `roles`; три Livewire-компоненти реєструються на `:87–89`. Режим manager — умова bootstrap, а не перевірка права користувача.
2. **Вхід через модуль.** `plugins/sSettingsPlugin.php:31–42` створює URL `index.php?a=112&id=<md5 title>&_token=...`. У CORE `manager/processors/execute_module.processor.php:6–11` перевіряє manager mode і `exec_module`, `:53–66` — реєстрацію файла й `ModuleAccess::canRunFileModule`. `CORE/core/src/Support/ModuleAccess.php:39–50,83–105` читає role-обмеження; порожній список дозволяє всі ролі, admin має bypass. `CORE/core/src/Core.php:7305–7319` переносить params реєстрації у `properties`. `hidden` саме по собі не обмежує виконання. Далі `module/sSettingsModule.php:5–7` викликає `sSettingsController::index()`.
3. **Прямі routes пакета.** `src/Http/routes.php:6–9` оголошує лише GET `ssettings/` та `ssettings/configure` з `mgr`. Власних POST/REST-шляхів значень у PKG не знайдено. Для першого route `src/Controllers/sSettingsController.php:14–19` приймає `tab` і не перевіряє `settings`; для другого `:27–34` перевірка є. Ці routes не оголошені через `registerRoutingModule`, тому автоматично приписувати їм `RoutingModuleAccess` не можна. У CORE цей захист прив'язаний до іншого способу реєстрації (`core/src/Core.php:7330–7341`, `core/src/Middleware/RoutingModuleAccess.php:21–42`).
4. **Manager-сесія й CSRF.** У CORE `core/config/app.php:151–157` включає session, CSRF та Manager middleware. `core/src/Middleware/Manager.php:10–31,43–67` перевіряє login і manager access; винятки входу/виходу залежать від dispatcher-route. `core/src/Middleware/VerifyCsrfToken.php:94–143` перевіряє токен для відповідних manager-запитів; action 112 є в переліку mutating GET (`:61`). `src/sSettings.php:35–54` та plugin додають токен у manager-посилання. Ці механізми не замінюють `settings`. У CORE `core/src/Core.php:3812–3823` це окремий прапорець у permissions потрібного контексту.
5. **Вибір компонента й початкове читання.** `views/index.blade.php:37` монтує ModulePanel. `src/Livewire/ModulePanel.php:20–27,39–48` читає схему. `:76–81` задає permission лише для configure-tab, але `:88–98` приймає `configure` без перевірки. `views/livewire/module-panel.blade.php:49–50` лише приховує кнопку; `:70–74` монтує ConfigurePanel або SettingsPanel за activeTab. У статичному ланцюзі `index?tab=configure` відсутня action-перевірка перед читанням схеми; `configure()` контролера цей шлях не проходить.
6. **Livewire update.** UI `src/Support/LivewireManagerEndpoint.php:13–25` передає `action=update` до `HandleRequests::handleUpdate()`. UI `src/EvoUIServiceProvider.php:152–155` налаштовує persistent middleware зі списку `mgr`, а `:174–199` реєструє endpoint-аліаси за manager mode. CORE `core/src/ManagerTheme.php:479–490` і `manager/routes.php:6–18` повторно оголошують обидва аліаси всередині `mgr`. Порядок реєстрації та overrides цільового сайту невідомі.
7. **Lifecycle не дорівнює повторному controller/index.** У DEP `livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:215–236,247–263` перевіряється checksum, відновлюються properties, викликаються hydrate, updates, public actions і render. `livewire/livewire/src/Features/SupportLifecycleHooks/SupportLifecycleHooks.php:25–59` розділяє mount і hydrate. `livewire/livewire/src/Mechanisms/HandleComponents/HandleComponents.php:580–593` допускає public methods підкласу, крім render; це саме по собі не надає permission. PKG SettingsPanel/ConfigurePanel/ModulePanel не мають власної перевірки доступу в boot/hydrate. ConfigurePanel перевіряє `settings` у save; SettingsPanel — ні.
8. **Межа persistent middleware.** DEP `livewire/livewire/src/Mechanisms/PersistentMiddleware/PersistentMiddleware.php:41–47,173–214` застосовує лише middleware початкового route, що є в persistent-списку; `:100–121` не викликає controller/processor. Додатково `livewire/livewire/src/Mechanisms/HandleRequests/HandleRequests.php:124–137` вимагає route name `*livewire.update`. Для кастомного endpoint це слід перевірити окремо: прямий виклик handleUpdate не доводить спрацювання persistent-механізму. Навіть повторений `mgr` не містить permission `settings` або коду `execute_module.processor.php`.
9. **Сервісні виклики.** У production-коді PKG знайдено один caller `saveValues()` — `SettingsPanel::save()` (`src/Livewire/SettingsPanel.php:41`) і один caller `syncSchema()` — `ConfigurePanel::save()` (`src/Livewire/ConfigurePanel.php:383`). `values()` викликається з `SettingsPanel::fillData()` (`:67`). Публічні сервіси також доступні довіреному PHP-коду через контейнер; самі вони не є HTTP-endpoint. Зовнішні callers, listeners, global scopes і middleware конкретного сайту не інвентаризовано.

Наявність manager- і module-захистів встановлена в довідковому коді. **Анонімний доступ, фактичний обхід ACL або ескалація не доведені.** Не встановлено, чи продукт навмисно дозволяє редакторам модуля змінювати значення без глобального `settings`.

## Перелік задач

SS-* — внутрішні записи цього backlog, а не нові історичні ID. Для додаткових варіантів наведено статус батьківської картки; окремого історичного підтвердження впливу витяг не містить. Поточне «конструкція є» описує текст коду. P1 — першочергова межа авторизації/збереження даних, P2 — наступне посилення контракту, P3 — уточнення без доведеного шкідливого ефекту. Пріоритет не змінює історичну severity.

| Запис | Сценарій | Історичний ID | Історична severity | Історичний статус | Поточний висновок | Пріоритет |
| --- | --- | --- | --- | --- | --- | --- |
| SS-01 | Читання значень | H-048 | HIGH | CONFIRMED картки | Немає локального `settings`; читаються всі поля схеми. Доступність залежить від зовнішніх шарів. | P1 |
| SS-02 | Читання схеми | H-048 | HIGH | CONFIRMED картки | Є шлях index/tab до configure без action-перевірки читання; значення це саме по собі не записує. | P1 |
| SS-03 | Збереження значень | H-048 | HIGH | CONFIRMED | Відсутність `settings` у save/saveValues збереглася; довільні ключі поза серверною схемою відсікаються. | P1 |
| SS-04 | Повний cache clear | H-048 | HIGH | CONFIRMED картки | Безумовний виклик `clearCache('full')` є; шкідливе навантаження не виміряне. | P2, разом із SS-03 |
| SS-05 | Downstream-використання | H-048 | HIGH | CONFIRMED картки | Є передача в system settings/config і документаційні приклади raw HTML. Конкретний небезпечний consumer не встановлено. | P2 |
| SS-06 | Історичний варіант lint | H-048 | HIGH | CONFIRMED картки; статус варіанта не деталізований | У цьому HEAD є `exec(php -l)`, всупереч повідомленню про заміну 06.11. Ін'єкцію команд не доведено. | P2: звірити походження |
| SS-07 | Видалення чужих ключів | L-006 | LOW | CONFIRMED | Загальне видалення поза `$owned` збереглося; owner/module scope немає. | P1: межі володіння |
| SS-08 | Порожня/неповна схема | L-006 | LOW | CONFIRMED картки | Немає явної заборони широкого pruning; порожня схема repository має fallback, але не гарантію повноти. | P1, разом із SS-07 |
| SS-09 | SQL LIKE і межа префікса | L-006 | LOW | CONFIRMED картки; цей нюанс окремо у витягу не описаний | `_` у `sset_%` не екранований; область може бути ширша за буквальний `sset_`. | P2, разом із SS-07 |
| SS-10 | Translation key зі схеми | L-006 | LOW | CONFIRMED картки; статус варіанта не деталізований | Ключі доходять до `__()`; довільне виконання/читання файла або інший шкідливий вплив не встановлено. | P3 |

### SS-01 — визначити право читання значень

**Історія:** H-048, HIGH, CONFIRMED картки. **Поточний результат:** конструкція є; кінцевий доступ потребує перевірки середовища й узгодження політики.

**Шлях даних:** manager/module або GET index → ModulePanel → `SettingsPanel::mount()` (`src/Livewire/SettingsPanel.php:26–29`) → `fillData()` (`:64–68`) → repository `read()` → store `values()` (`src/Support/SystemSettingsStore.php:17–33`) → `evo()->getConfig('sset_' . name)` → public `schema`, `data`, `cleanData` (`SettingsPanel.php:16–21`). Завантажуються всі definitions схеми, хоча шаблон показує лише активну вкладку (`views/livewire/settings-panel.blade.php:128–140`). Перемикання вкладки не є ізоляцією даних.

**Права та захисти:** manager-сесія/manager access у CORE; для module-entry — `exec_module` і file-module ACL. Усередині читання немає `hasPermission('settings', 'mgr')`. Доступ до конфігурації PHP-сервісом потрібен frontend; обмеження слід ставити на manager-видачу, не механічно блокувати всі `getConfig()`.

**Передумови й невідоме:** користувач без `settings` має дійти до компонента; конкретні значення можуть бути публічними контактами або чутливими налаштуваннями. Склад схеми й secrets цільового сайту не читалися. Передача public state браузеру очікується за lifecycle DEP, але реальний response не перевірявся.

**Напрям і приймання:** затвердити окреме read/edit право або свідомо використати `settings`; перевіряти право до завантаження/серіалізації даних і на наступних запитах після зміни прав. Визначити, чи користувач має бачити всі вкладки. Недозволений читач не отримує значень у HTML, snapshot, errors або effects.

**Майбутні тести:** позитивний — дозволений читач отримує лише дозволені поля, включно з locale fallback. Негативні — manager без read-права, роль без доступу до модуля, гість, відкликане право після mount; перевірити повний payload, а не лише видимість input. Окремо перевірити прямий index-route й module-entry.

### SS-02 — захистити читання схеми незалежно від кнопки configure

**Історія:** H-048, HIGH, CONFIRMED картки. **Поточний результат:** окремий шлях читання без action-перевірки є в коді.

**Шлях:** `sSettingsController::index()` бере `tab` (`src/Controllers/sSettingsController.php:16–19`) → ModulePanel приймає `configure` (`src/Livewire/ModulePanel.php:88–95`) → шаблон монтує ConfigurePanel (`views/livewire/module-panel.blade.php:70–73`) → `ConfigurePanel::mount()` (`src/Livewire/ConfigurePanel.php:28–30`) → `fillData()`/`fillDataFromSchema()` (`:408–455`). Схема також читається ModulePanel для вкладок (`ModulePanel.php:45–51`) і SettingsPanel для значень. `SettingsSchemaRepository::read()` (`src/Support/SettingsSchemaRepository.php:42–56`) використовує фіксований локальний PHP-файл `custom/config/seiger/settings/sSettings.php` (`:13,20–26`), а не шлях з HTTP.

**Права та захисти:** GET configure-controller перевіряє `settings` (`sSettingsController.php:29–32`); меню приховує configure-кнопку (`views/livewire/module-panel.blade.php:50`). `ConfigurePanel::save()` повторює право (`:370–374`). Перевірки збереження не закривають усі читання схеми. `index?tab=configure` тут — статично простежений шлях, не виконаний HTTP-запит.

**Передумови й невідоме:** потрібен доступ до manager-сторінки; показ структури/назв полів може бути допустимим для редактора значень. Це не доказ читання довільного файла або запису PHP-схеми без `settings`.

**Напрям і приймання:** визначити дозволену частину схеми для read/editor; закрити configure-read на рівні компонента/lifecycle та нормалізації activeTab, зберігши server-side save guard. Авторизувати кожний спосіб входу, включно з direct tab/public action.

**Майбутні тести:** позитивний — schema-editor відкриває configure звичайним route, через index/tab і switchTab. Негативні — редактор значень без schema-права не отримує configure-state через query, hydration або зміну public activeTab; прямий save повертає відмову без запису файла й без syncSchema.

### SS-03 — авторизувати збереження значень на кожній дії

**Історія:** H-048, HIGH, CONFIRMED; старі орієнтири `SettingsPanel.php:38–46`, `SystemSettingsStore.php:40`. **Поточний результат:** відсутність перевірки збереглася саме у цих методах.

**Шлях:** `views/livewire/settings-panel.blade.php:12–24` → `$wire.save()` → `SettingsPanel::save()` (`src/Livewire/SettingsPanel.php:38–45`) → `$schema->read()` і `$this->data` → `SystemSettingsStore::saveValues()` (`src/Support/SystemSettingsStore.php:40–61`) → `firstOrCreate`, `save`, `setConfig`; default locale також оновлює базовий ключ (`:54–57`). Сервіс не перевіряє користувача. `CORE/core/src/Models/SystemSetting.php:14–27` задає таблицю/поля, не `settings`-policy; прочитаний ManagerActions trait генерує URL (`CORE/core/src/Traits/Models/ManagerActions.php:5–39`), а не дозвіл на ці записи. Зовнішні model hooks невідомі.

**Наявні захисти:** схема перечитується із сервера; public `$schema` клієнта не використовується як allowlist запису. Обробляються тільки `fieldDefinitions($schema)`; сторонні submitted names і відсутні значення пропускаються (`SystemSettingsStore.php:42–47`). Є нормалізація значень (`:192–198`), перевірка multilingual-конфліктів (`:127–157`). Немає доведеного запису довільних core settings. CSRF/checksum захищають інші межі, не визначають permission дії.

**Передумови й невідоме:** потрібні досяжний компонент, припустимий Livewire-запит і сесія, яка проходить установлені middleware. Чи може конкретна роль без `settings` це зробити на цільовому сайті, не перевірено. Можливо, потрібна окрема permission редагування значень замість глобального права схеми.

**Напрям і приймання:** узгодити політику read/value-write/schema-write; застосувати server-side guard перед будь-якими side effects кожного save, включно з повторними діями після відкликання права. Визначити контракт для довірених PHP-callers сервісу. Відмова не змінює DB/config, не очищає кеш і не надсилає successful saved-event.

**Майбутні тести:** позитивні — дозволена роль зберігає звичайне, multiple і localized поле; пропущена locale зберігає попереднє значення. Негативні — manager без value-write, роль із `exec_module`, але без write-права, втрачений module access/permission після mount, гість/прострочена сесія, неправильний CSRF/checksum, прямий action без кнопки. Окремо подавати чужий `sset_*` і core key поза серверною схемою: вони не змінюються.

### SS-04 — звузити й обґрунтувати інвалідацію кешу

**Історія:** H-048, HIGH, CONFIRMED картки; повне очищення — пов'язане твердження. **Поточний результат:** виклик підтверджений статично.

**Шлях і наслідок:** `SystemSettingsStore::saveValues()` безумовно викликає `evo()->clearCache('full')` (`src/Support/SystemSettingsStore.php:61`), навіть якщо цикл не записав жодного поданого значення. Те саме робить `syncSchema()` (`:109`). У CORE `core/src/Core.php:4757–4778` видаляє compiled views, викликає Legacy Cache та інвалідує env cache. `CORE/core/src/Legacy/Cache.php:113–128` має `Cache::flush()`, очищення page cache, умовний `opcache_reset()` і rebuild. Це ширше за один ключ sSettings; не означає очищення будь-якого зовнішнього CDN/кешу.

**Права, захисти, невідоме:** успадковує доступ SS-03; schema-sync має `settings` guard caller. UI `saving/localDirty/savedClean` (`views/livewire/settings-panel.blade.php:12–17`) обмежує звичайну кнопку, але сервісного no-op guard немає. Навантаження, backend кешу, частота викликів і можливість відмови в обслуговуванні не перевірені.

**Напрям і приймання:** спершу закрити авторизацію; визначити мінімальну достатню інвалідацію після фактичної зміни. Відмова й no-op не очищають повний кеш; успішна зміна стає видимою всім потрібним consumers. Помилка запису не оголошується успіхом.

**Майбутні тести:** позитивний — змінене значення видно наступному запиту після потрібного invalidation. Негативні — empty/незмінений payload, недозволена дія та невдалий запис не викликають full clear; повторний save не створює зайвого очищення. Частковий запис перевіряти в ізольованому середовищі.

### SS-05 — перевірити downstream-контексти без припущення про експлуатацію

**Історія:** H-048, HIGH, CONFIRMED картки; downstream-вплив окремо не доведений. **Поточний результат:** канал передачі є, конкретний небезпечний sink цільового сайту невідомий.

**Шлях:** значення → `system_settings.setting_value` та runtime config (`src/Support/SystemSettingsStore.php:49–57`) → `getConfig()`. CORE `core/src/Traits/Settings.php:80–115` повертає конфігурацію без user permission; це очікуваний frontend API. `CORE/core/src/Providers/BladeServiceProvider.php:44–46` реалізує `@evoConfig` з `e()`. Натомість `CORE/core/src/Core.php:1799–1844` підставляє системні теги `[(...)]` з config і може застосовувати modifiers. Документація PKG показує escaped Blade-посилання та **raw HTML** для `sset_google_analytics` (`docs/ua/frontend-guide.md:17–43`); відповідне textarea є в default schema (`config/sSettingsSettings.php:23–27`). Це приклад використання, не знайдений production-template.

**Передумови й захисти:** потенційний вплив вимагає права/можливості змінювати відповідне поле та consumer, який довіряє цьому значенню. Нормалізація scalar до trimmed string (`SystemSettingsStore.php:192–198`) не є HTML-, URL- або script-policy. Escaped `@evoConfig` зменшує HTML-ризик, але не замінює перевірку URL-схеми чи правил конкретного sink. Підставляти факт XSS/RCE/ескалації за самим `getConfig()` не можна.

**Напрям і приймання:** інвентаризувати фактичні consumers після окремого дозволу на доступ до сайту; класифікувати plain text, URL/path, rich HTML/script та інші контексти. Для кожного визначити потрібну permission, валідацію й escaping. Довірені script-поля мають бути свідомим контрактом із належним write-правом.

**Майбутні тести:** позитивні — звичайний текст, дозволений URL і свідомо дозволений rich content зберігають потрібну поведінку. Негативні — спецсимволи/HTML у plain-text sink, недозволені URL-схеми, script-вміст від ролі без відповідного права не набувають активної поведінки. Перевіряти кожний реальний consumer окремо.

### SS-06 — звірити lint-реалізацію та походження розбіжності

**Історія:** H-048, HIGH, CONFIRMED батьківської картки; витяг 06.11 повідомляє заміну `exec(php -l)` на `token_get_all` у SettingsSchemaRepository. Окремий статус варіанта не наданий. **Поточний результат:** у перевіреному HEAD заміни немає.

**Доказ і шлях:** `ConfigurePanel::save()` перевіряє `settings` (`src/Livewire/ConfigurePanel.php:370–382`) → repository `save()` → normalize → `var_export($schema, true)` → fixed `<config>.tmp` із `LOCK_EX` (`src/Support/SettingsSchemaRepository.php:63–81`) → `if (function_exists('exec'))` → `exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($tmp), ...)` (`:84–89`) → rename (`:92–97`). `token_get_all` у цьому файлі не знайдено.

**Захисти й невідоме:** обидва аргументи shell екрановані, PHP-представлення даних генерується через `var_export`, шлях не береться з HTTP, збереження схеми має permission guard. Довільну командну ін'єкцію чи виконання поданого PHP не встановлено. Чи це інша гілка/версія порівняно з архівом, невідомо; факт наявності exec не є доказом нового регресу. За відсутності exec lint пропускається.

**Напрям і приймання:** отримати точний архівний commit/patch і зіставити його без автоматичного перенесення історії на цю гілку. Після узгодження вибрати перевірку синтаксису без shell або явно обґрунтувати поточну. Якщо обрано `token_get_all`, визначити режим виявлення синтаксичних помилок; просте токенізування не слід вважати еквівалентом `php -l` без перевірки. Невалідний payload не замінює робочу схему; помилка очищує тимчасовий файл.

**Майбутні тести:** позитивні — валідна схема, Unicode, лапки та multiline зберігаються коректно. Негативні — синтаксично невалідний згенерований payload відхиляється, невдалий write/rename не псує старий файл, роль без `settings` не запускає lint і не пише файл. Тести shell-викликів планувати із заміною виконавця, не на робочому сайті.

### SS-07 — обмежити видалення ключів реальним володінням

**Історія:** L-006, LOW, CONFIRMED; орієнтир `SystemSettingsStore.php:104–107`. **Поточний результат:** конструкція видалення збереглася; основний ризик — **втрата чужих ключів і значень**.

**Шлях:** `ConfigurePanel::save()` → `hasPermission('settings', 'mgr')` → normalize/fieldDefinitions → repository save → `syncSchema($normalized)` (`src/Livewire/ConfigurePanel.php:368–383`). У `src/Support/SystemSettingsStore.php:69–81` `$owned` формується з поточних stored field definitions: `sset_` + name, із створенням відсутніх рядків. `:84–102` додає базові ключі та наявні language-suffix ключі, якщо їх базове поле ще є. `:104–107` видаляє всі рядки, що відповідають LIKE і не належать `$owned`. `query()` (`:204–206`) повертає загальний SystemSetting query; module/owner filter немає.

**Межі й захисти:** `fieldDefinitions()` відкидає divider/порожні definitions і перевіряє multilingual-конфлікти (`:116–159`); `localizedFields()` (`:163–176`) та `src/Support/SettingsLanguages.php:12–27` визначають локалі. Поки базове поле існує, архівні locale-значення зберігаються навіть після вимкнення мови/multilingual. Проте `$owned` — список бажаних поточних ключів, а не історичний реєстр того, хто створив записи. Немає перевірки власника іншого модуля або захисту від однакових назв полів різних writers. Public `syncSchema()` сам permission не перевіряє; у PKG production-caller один і має guard.

**Передумови й невідоме:** потрібен schema-save з правом `settings` і writable config або довірений PHP-caller сервісу. Потрібні наявні чужі ключі в області LIKE, відсутні в `$owned`. Їх існування на конкретному сайті не встановлено. Єдине право `settings` не доводить згоду користувача видаляти налаштування інших модулів. Ризик стосується цілісності даних, не шаблонної категорії «витік інформації».

**Напрям і приймання:** визначити namespace/реєстр володіння та явний diff старої й нової власної схеми. Видаляти тільки записи, для яких установлено право sSettings на pruning; розрізнити rename, видалення поля й втрату частини definitions. Забезпечити перегляд плану видалення та відновлення за погодженим контрактом. Чужі ключі залишаються byte-for-byte незмінними; мовні значення не втрачаються при зміні locale-конфігурації.

**Майбутні тести:** позитивні — нові власні ключі створюються; дозволене видалення конкретного власного поля видаляє лише погоджені його ключі. Негативні — два модулі з `sset_*`, колізія імен, ключ без відомого owner, відсутність permission/writable file, повторний sync не видаляють чужі дані. Окремо зберігати активні/архівні locale-ключі наявного поля й тестувати його перехід у divider/перейменування як явну destructive-зміну.

### SS-08 — зробити порожню та неповну схему безпечною для даних

**Історія:** L-006, LOW, CONFIRMED батьківської картки. **Поточний результат:** широке pruning можливе за структурою алгоритму; кількість видалених рядків не перевірена.

**Варіанти й шлях:**

- Прямий `syncSchema([])` або таб із порожніми fields дає порожні definitions/baseNames (`src/Support/SystemSettingsStore.php:69–73,86–94`); явного empty guard перед delete (`:104–107`) немає. У довідковому DEP `illuminate/database/Query/Grammars/Grammar.php:395–401` порожній `whereNotIn` компілюється в `1 = 1`: лишається область LIKE. Фактична бібліотека/SQL сайту не перевірені.
- Repository `normalize([])` повертає default schema (`src/Support/SettingsSchemaRepository.php:157`); це **не** завжди порожній `$owned`. Поточний default містить email/phone/phones/google_analytics і socials (`config/sSettingsSettings.php:1–60`), але ключів інших схем він не зберігає. Непорожній таб без fields лишається порожнім, а divider не зберігає значення (`src/Support/FieldCatalog.php:101–103`).
- Відсутній schema-файл дає default (`SettingsSchemaRepository.php:46–47`); Throwable/не-array результат перетворюється на порожній input і також нормалізується (`:50–56`). Сам `read()` не видаляє DB-ключів. Ризик виникає при наступному збереженні fallback/часткової схеми й sync через configure. Default у крайньому fallback теж допускає `fields => []` (`:161–170`).
- Часткова схема A без поля B не відрізняється для sync від навмисного видалення B. Файл зберігається **до** DB-sync (`ConfigurePanel.php:382–383`), а catch повертає помилку без видимого відновлення попереднього файла (`:391–393`); узгодженість файла й БД при збоях невідома.

**Права та захисти:** той самий schema-write guard, нормалізація, перевірка конфліктів до запису файла. UI забороняє видалити останній tab (`src/Livewire/ConfigurePanel.php:63–71`), але це не гарантія непорожніх definitions чи повноти public tabs. `tests/multilingual.php:91–92` уже очікує нуль рядків після порожніх fields у вузькій тестовій БД; це не перевірка співіснування модулів і не виконаний тут тест.

**Напрям і приймання:** відрізняти помилку читання, fallback, валідну порожню схему та явне видалення. За невизначеної повноти не виконувати destructive sync. Установити узгоджений порядок/відновлення file+DB; за збою залишати відновлюваний попередній стан. Легітимне очищення лише власних ключів має бути окремо визначеною дією.

**Майбутні тести:** позитивні — повна схема синхронізується; погоджене очищення власної схеми не зачіпає чужі ключі. Негативні — missing/broken/non-array config, `[]`, таб без fields, лише divider, часткова схема, помилка DB після file-save, конфлікт locale і stale schema з другого вікна не спричиняють непогодженого видалення. Використовувати лише спеціально створені fixtures майбутнього тестового середовища.

### SS-09 — перевірити буквальний префікс у SQL LIKE

**Історія:** L-006, LOW, CONFIRMED картки; окремого історичного твердження про wildcard витяг не містить. **Поточний результат:** додаткове статичне уточнення області того самого delete.

**Доказ:** `where('setting_name', 'like', 'sset_%')` використано і для збору мовних ключів (`src/Support/SystemSettingsStore.php:95`), і для delete (`:105`). У стандартній SQL LIKE семантиці `_` означає один довільний символ; literal underscore тут явно не екрановано. Кандидатами можуть бути також `ssetXforeign`, а не лише `sset_foreign`. Це не SQL injection: шаблон константний. Такий ключ не потрапляє в `$owned`, що будується з буквального `sset_`.

**Передумови, захисти, невідоме:** потрібен sync із попереднього сценарію та такі рядки в БД. Драйвер, collation/escape-правила й реальний набір ключів не читалися. Інші middleware не звужують SQL-область володіння. Поточний caller має `settings`.

**Напрям і приймання:** забезпечити буквальну перевірку префікса під підтримуваний драйвер і поєднати її з owner-scope SS-07; сама заміна LIKE не розв'язує міжмодульне володіння. Жоден ключ поза буквальним namespace і дозволеним owner не змінюється.

**Майбутні тести:** позитивний — дозволений власний `sset_alpha` обробляється за контрактом sync. Негативні — `ssetXalpha`, `sset`, `ssettings`, чужий `sset_alpha` та Unicode/case-варіанти не видаляються випадково; перевірити фактичний SQL на кожному підтримуваному драйвері в майбутньому тестовому середовищі.

### SS-10 — визначити контракт translation keys із PHP-схеми

**Історія:** L-006, LOW, CONFIRMED батьківської картки; окремого статусу й доведеного впливу варіанта немає. **Поточний результат:** передача до `__()` є, шкідливий вплив не встановлений.

**Шлях:** PHP-schema `read()`/normalize → `normalizeLabel()` зберігає рядки з `::` (`src/Support/SettingsSchemaRepository.php:207–215`), відомі literal labels перетворює на package key (`:218–245`) → label вкладки через `ModulePanel::translation()` (`src/Livewire/ModulePanel.php:67,101–105`) → labels/descriptions/options у `views/livewire/settings-panel.blade.php:141–145,175,186` → `__()`. ConfigurePanel теж викликає `displayText()` для labels/descriptions (`src/Livewire/ConfigurePanel.php:439–448,677–685`). CORE helper `core/functions/laravel.php:431–437` передає ключ до `trans()`; тут не показано виконання PHP із самого ключа.

**Права й захисти:** persisted-схему штатно зберігає дія з `settings`; альтернативою є довірений запис PHP-файла. Значення звичайного input не є label-key у серверній схемі. Основні labels/options виводяться через escaped Blade `{{ }}` (`views/livewire/settings-panel.blade.php:155,160,175`), а checkbox-description проходить окремий `DescriptionHtml::render` (`:168`), повний аудит якого тут не проводився. Translation keys самі по собі не доводять доступ до довільного файла, витік або виконання коду. Завантажувачі перекладів/додаткові namespaces цільового сайту невідомі; виконання PHP-схеми через require — окрема довірча межа файла, не наслідок `__()`.

**Напрям і приймання:** вирішити, чи дозволені довільні зареєстровані namespaces, чи лише визначений список; розрізнити literal text і translation key. Невідомий ключ/не-string переклад має передбачуваний fallback без необробленого винятку або активного HTML. Не вводити allowlist без узгодження сумісності існуючих схем.

**Майбутні тести:** позитивні — package key, дозволений зовнішній namespace, literal Ukrainian label й зміна locale відображаються коректно. Негативні — missing key, array-valued translation, недозволений namespace і HTML у label/description обробляються за погодженим контрактом. Умови й очікуваний ефект слід фіксувати для конкретного translator/renderer; не називати успішний lookup експлуатацією.

## Невирішені питання та порядок наступної роботи

1. **Політика прав (SS-01–03):** чи `settings` має керувати значеннями, схемою й читанням, чи потрібні окремі abilities? Яким ролям навмисно надано `exec_module` та доступ до sSettings без schema-write?
2. **Володіння й pruning (SS-07–09):** чи `sset_` зарезервовано виключно для цього модуля? Які інші writers існують? Як відрізняти зниклу/часткову схему від явного видалення й відновлювати file+DB після збою?
3. **Середовище:** які commits/версії CORE, EvoUI, Livewire та illuminate/database реально встановлено, які custom middleware/model hooks діють, який endpoint/name використовується для update? Чи перевіряється поточний module access після відкликання прав? ROOT I-006 залишається NOT_DETERMINED.
4. **Походження аудиту (SS-06):** потрібні повний архів і точний commit заміни exec на token_get_all. Перевірений HEAD відрізняється від опису 06.11; причину не встановлено.
5. **Вплив (SS-04–05, SS-10):** які чутливі поля й реальні consumers є на сайті, які translation namespaces допустимі, чи потрібен повний cache clear? Жодних secrets або production-значень цей перегляд не читав.

Починати з узгодження політики доступу та меж володіння, потім реалізовувати окремі зміни з наведеними критеріями. Майбутні позитивні тести мають підтверджувати дозволену поведінку, негативні — відсутність читання/запису/видалення/side effects для забороненого сценарію. У цьому завданні **жоден із запропонованих тестів не реалізовано й не виконано**.

## Результат цієї звірки

- **H-048:** відсутність локального `settings` у читанні й збереженні значень актуальна; schema-save має власний guard, manager/module-захисти в довідковому ядрі існують. Кінцеву досяжність, експлуатацію й ескалацію не доведено. Full cache clear підтверджений кодом; downstream-ефект залежить від consumers. У поточному HEAD є exec-lint, попри описану в матеріалах заміну.
- **L-006:** загальний prune поза `$owned` актуальний; він не встановлює міжмодульного володіння. Empty/partial/fallback-схеми та LIKE wildcard потребують окремого захисту. Translation lookup розглянутий окремо без приписування непідтвердженого впливу.
- Проблеми не позначені виправленими за зміною рядків або файлів. **Код, конфігурація, залежності, тести й наявні документи не змінювалися; runtime-перевірки не виконувалися.**
