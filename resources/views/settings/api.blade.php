@extends('layouts.app')

@section('title', '')

@section('topbar_brand')
    <div class="api-settings-topbar-title">API Settings</div>
@endsection

@section('content')
@include('partials.crud-package-theme')

<style>
    .api-settings-topbar-title {
        color: #173761;
        font-size: 2rem;
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.04em;
        font-family: "Segoe UI", "Trebuchet MS", "Helvetica Neue", Arial, sans-serif;
    }

    .api-settings-shell {
        max-width: 960px;
        background: #fff;
        border: 1px solid #dbe8ff;
        border-radius: 8px;
        box-shadow: 0 12px 30px rgba(16, 35, 59, 0.06);
        overflow: hidden;
    }

    .api-settings-head {
        padding: 1rem 1.2rem;
        border-bottom: 1px solid rgba(137, 167, 214, 0.22);
        background: linear-gradient(180deg, #f2f7ff 0%, #e8f1ff 100%);
    }

    .api-settings-head h3 {
        margin: 0;
        color: #173761;
        font-size: 1.15rem;
        font-weight: 800;
    }

    .api-settings-head p {
        margin: 0.28rem 0 0;
        color: #60748f;
        font-size: 0.9rem;
    }

    .api-settings-body {
        padding: 1.2rem;
    }

    .api-settings-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .api-settings-field {
        display: grid;
        gap: 0.45rem;
    }

    .api-settings-credentials {
        grid-column: span 2;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
        align-items: start;
    }

    .api-settings-credentials .api-settings-field {
        grid-template-rows: auto calc(2.7rem + 2px) auto;
    }

    .api-settings-credentials .package-input {
        width: 100%;
    }

    .api-settings-field-wide {
        grid-column: span 2;
    }

    .api-settings-field label {
        margin: 0;
        color: #173761;
        font-size: 0.92rem;
        font-weight: 700;
    }

    .api-settings-note {
        margin-top: 0.5rem;
        color: #60748f;
        font-size: 0.86rem;
        line-height: 1.45;
    }

    .api-settings-actions {
        display: flex;
        justify-content: flex-end;
        gap: 0.75rem;
        margin-top: 1.25rem;
        flex-wrap: wrap;
    }

    .api-tester-shell {
        max-width: 1180px;
        margin-top: 1.25rem;
        background: #fff;
        border: 1px solid #dbe8ff;
        border-radius: 8px;
        box-shadow: 0 12px 30px rgba(16, 35, 59, 0.06);
        overflow: hidden;
    }

    .api-tester-toolbar {
        display: grid;
        grid-template-columns: 160px 140px minmax(220px, 1fr) auto;
        gap: 0.75rem;
        padding: 1rem 1.2rem;
        border-bottom: 1px solid rgba(137, 167, 214, 0.22);
        background: #f6f9ff;
    }

    .api-tester-body {
        display: grid;
        grid-template-columns: minmax(300px, 0.85fr) minmax(360px, 1.15fr);
        gap: 1rem;
        padding: 1.2rem;
    }

    .api-tester-panel {
        min-width: 0;
        display: grid;
        gap: 1rem;
        align-content: start;
    }

    .api-tester-section {
        display: grid;
        gap: 0.65rem;
    }

    .api-tester-section-title {
        color: #173761;
        font-size: 0.95rem;
        font-weight: 800;
    }

    .api-tester-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .api-tester-field {
        display: grid;
        gap: 0.4rem;
    }

    .api-tester-field-wide {
        grid-column: 1 / -1;
    }

    .api-tester-field label {
        margin: 0;
        color: #173761;
        font-size: 0.85rem;
        font-weight: 700;
    }

    .api-tester-tabs {
        display: inline-flex;
        gap: 0.25rem;
        padding: 0.25rem;
        border: 1px solid #dbe8ff;
        border-radius: 8px;
        background: #f6f9ff;
        width: fit-content;
    }

    .api-tester-tab {
        border: 0;
        border-radius: 6px;
        color: #55708f;
        background: transparent;
        font-weight: 800;
        padding: 0.42rem 0.75rem;
        cursor: pointer;
    }

    .api-tester-tab.active {
        color: #fff;
        background: #173761;
    }

    .api-tester-textarea,
    .api-tester-output {
        width: 100%;
        min-height: 190px;
        resize: vertical;
        border: 1px solid #dbe8ff;
        border-radius: 8px;
        padding: 0.75rem;
        font-family: Consolas, "Courier New", monospace;
        font-size: 0.88rem;
        line-height: 1.5;
        background: #fbfdff;
        color: #173761;
    }

    .api-tester-output {
        min-height: 430px;
        white-space: pre-wrap;
        overflow: auto;
    }

    .api-tester-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
        color: #60748f;
        font-size: 0.86rem;
    }

    .api-tester-status {
        font-weight: 800;
    }

    .api-tester-status.ok {
        color: #12805c;
    }

    .api-tester-status.error {
        color: #b42318;
    }

    .api-tester-note {
        color: #60748f;
        font-size: 0.86rem;
        line-height: 1.45;
    }

    @media (max-width: 767.98px) {
        .api-settings-grid {
            grid-template-columns: 1fr;
        }

        .api-settings-field-wide {
            grid-column: span 1;
        }

        .api-settings-credentials {
            grid-column: span 1;
            grid-template-columns: 1fr;
        }

        .api-tester-toolbar,
        .api-tester-body,
        .api-tester-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container-fluid px-0">
    @if (session('success'))
        <div class="alert package-alert mb-4" id="successAlert">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="/settings/api" class="api-settings-shell">
        @csrf
        <div class="api-settings-head">
            <h3>Global CRUD API Access</h3>
            <p>Applies to every endpoint inside /api/v1 that uses the shared CRUD API middleware.</p>
        </div>

        <div class="api-settings-body">
            <div class="api-settings-grid">
                <div class="api-settings-field api-settings-field-wide">
                    <label for="auth_mode">Authentication Mode</label>
                    <select name="auth_mode" id="auth_mode" class="form-control package-input">
                        <option value="basic" {{ ($settings['auth_mode'] ?? 'basic') === 'basic' ? 'selected' : '' }}>Basic Auth</option>
                        <option value="token" {{ ($settings['auth_mode'] ?? 'basic') === 'token' ? 'selected' : '' }}>Bearer Token</option>
                    </select>
                    <div class="api-settings-note">
                        Basic Auth accepts the API username/password below when filled. If left empty, it accepts existing application users from SANDI. Bearer Token uses /api/v1/login and Authorization: Bearer.
                    </div>
                </div>

                <div class="api-settings-credentials">
                    <div class="api-settings-field">
                        <label for="basic_username">Basic Username</label>
                        <input type="text" name="basic_username" id="basic_username" class="form-control package-input"
                            value="{{ old('basic_username', $settings['basic_username'] ?? '') }}" autocomplete="off">
                    </div>

                    <div class="api-settings-field">
                        <label for="basic_password">Basic Password</label>
                        <input type="password" name="basic_password" id="basic_password" class="form-control package-input"
                            value="" autocomplete="new-password">
                        <div class="api-settings-note">
                            {{ !empty($settings['has_static_basic_credential']) ? 'Password is already set. Leave blank to keep it.' : 'Leave blank to use SANDI user passwords.' }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="api-settings-actions">
                <button type="submit" class="btn package-btn-primary">Save API Settings</button>
            </div>
        </div>
    </form>

    <div class="api-tester-shell" id="apiTester">
        <div class="api-settings-head">
            <h3>CRUD API Tester</h3>
            <p>Test GET, POST, PUT, and DELETE requests directly from this page.</p>
        </div>

        <div class="api-tester-toolbar">
            <select id="apiTesterResource" class="form-control package-input" aria-label="API resource">
                <option value="login">Login</option>
                <option value="kelas">Kelas</option>
                <option value="room">Room</option>
                <option value="item-package-global">Item Package</option>
                <option value="menu-package-transaction">Package Transaction</option>
                <option value="checkin">Checkin</option>
                <option value="checkout">Checkout</option>
                <option value="custom">Custom URL</option>
            </select>
            <select id="apiTesterMethod" class="form-control package-input" aria-label="HTTP method">
                <option value="GET">GET</option>
                <option value="POST">POST</option>
                <option value="PUT">PUT</option>
                <option value="DELETE">DELETE</option>
            </select>
            <input type="text" id="apiTesterUrl" class="form-control package-input" value="/api/v1/kelas" aria-label="Request URL">
            <button type="button" class="btn package-btn-primary" id="apiTesterSend">Send</button>
        </div>

        <div class="api-tester-body">
            <div class="api-tester-panel">
                <div class="api-tester-section">
                    <div class="api-tester-section-title">Authorization</div>
                    <div class="api-tester-grid">
                        <div class="api-tester-field">
                            <label for="apiTesterAuthType">Type</label>
                            <select id="apiTesterAuthType" class="form-control package-input">
                                <option value="none">No Auth</option>
                                <option value="basic" {{ ($settings['auth_mode'] ?? 'basic') === 'basic' ? 'selected' : '' }}>Basic Auth</option>
                                <option value="bearer" {{ ($settings['auth_mode'] ?? 'basic') === 'token' ? 'selected' : '' }}>Bearer Token</option>
                            </select>
                        </div>
                        <div class="api-tester-field">
                            <label for="apiTesterToken">Bearer Token</label>
                            <input type="password" id="apiTesterToken" class="form-control package-input" autocomplete="off">
                        </div>
                        <div class="api-tester-field">
                            <label for="apiTesterUsername">Username</label>
                            <input type="text" id="apiTesterUsername" class="form-control package-input"
                                value="{{ $settings['basic_username'] ?? '' }}" autocomplete="off">
                        </div>
                        <div class="api-tester-field">
                            <label for="apiTesterPassword">Password</label>
                            <input type="password" id="apiTesterPassword" class="form-control package-input" autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="api-tester-section">
                    <div class="api-tester-section-title">Headers</div>
                    <textarea id="apiTesterHeaders" class="api-tester-textarea" spellcheck="false">{
  "Accept": "application/json",
  "Content-Type": "application/json"
}</textarea>
                </div>

                <div class="api-tester-section">
                    <div class="api-tester-meta">
                        <div class="api-tester-section-title">Body</div>
                        <div class="api-tester-tabs" role="tablist" aria-label="Body examples">
                            <button type="button" class="api-tester-tab active" data-example="empty">Empty</button>
                            <button type="button" class="api-tester-tab" data-example="post">POST</button>
                            <button type="button" class="api-tester-tab" data-example="put">PUT</button>
                        </div>
                    </div>
                    <textarea id="apiTesterBody" class="api-tester-textarea" spellcheck="false"></textarea>
                    <div class="api-tester-note">For PUT and DELETE, include the record key in the URL, for example /api/v1/kelas/DLX.</div>
                </div>
            </div>

            <div class="api-tester-panel">
                <div class="api-tester-meta">
                    <div>
                        <span class="api-tester-section-title">Response</span>
                        <span id="apiTesterStatus" class="api-tester-status"></span>
                    </div>
                    <span id="apiTesterTime"></span>
                </div>
                <pre id="apiTesterOutput" class="api-tester-output">Click Send to test an API endpoint.</pre>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const resourceInput = document.getElementById('apiTesterResource');
        const methodInput = document.getElementById('apiTesterMethod');
        const urlInput = document.getElementById('apiTesterUrl');
        const authTypeInput = document.getElementById('apiTesterAuthType');
        const tokenInput = document.getElementById('apiTesterToken');
        const usernameInput = document.getElementById('apiTesterUsername');
        const passwordInput = document.getElementById('apiTesterPassword');
        const headersInput = document.getElementById('apiTesterHeaders');
        const bodyInput = document.getElementById('apiTesterBody');
        const sendButton = document.getElementById('apiTesterSend');
        const statusOutput = document.getElementById('apiTesterStatus');
        const timeOutput = document.getElementById('apiTesterTime');
        const responseOutput = document.getElementById('apiTesterOutput');
        const tabs = document.querySelectorAll('.api-tester-tab');

        const resources = {
            login: {
                baseUrl: '/api/v1/login',
                key: '',
                method: 'POST',
                authType: 'none',
                post: {
                    username: usernameInput.value || 'casmidi',
                    password: ''
                },
                put: {}
            },
            kelas: {
                baseUrl: '/api/v1/kelas',
                key: 'TEST',
                post: {
                    Kode: 'TEST',
                    Nama: 'TEST API',
                    Rate1: 250000,
                    Depo1: 100000
                },
                put: {
                    Nama: 'TEST API UPDATED',
                    Rate1: 275000,
                    Depo1: 125000
                }
            },
            room: {
                baseUrl: '/api/v1/room',
                key: 'TST',
                post: {
                    Kode: 'TST',
                    Nama: 'DLX',
                    Fasilitas: 'TEST ROOM',
                    ExtNo: '100',
                    KUNCI: 'TST',
                    Rate1: 350000,
                    Rate2: 315000
                },
                put: {
                    Nama: 'DLX',
                    Fasilitas: 'TEST ROOM UPDATED',
                    ExtNo: '101',
                    KUNCI: 'TST',
                    Rate1: 375000,
                    Rate2: 335000
                }
            },
            'item-package-global': {
                baseUrl: '/api/v1/item-package-global',
                key: 'TESTITEM',
                post: {
                    KodeBrg: 'TESTITEM',
                    NamaBrg: 'TEST ITEM',
                    Satuan: 'PAX',
                    Kind: 'ROOM',
                    Hj: 50000
                },
                put: {
                    NamaBrg: 'TEST ITEM UPDATED',
                    Satuan: 'PAX',
                    Kind: 'ROOM',
                    Hj: 55000
                }
            },
            'menu-package-transaction': {
                baseUrl: '/api/v1/menu-package-transaction',
                key: 'TESTPKG',
                post: {},
                put: {}
            },
            checkin: {
                baseUrl: '/api/v1/checkin',
                key: 'REGNO2',
                post: {},
                put: {}
            },
            checkout: {
                baseUrl: '/api/v1/checkout',
                key: '',
                post: {},
                put: {}
            }
        };

        function currentResource() {
            return resources[resourceInput.value] || resources.kelas;
        }

        function bodyExamples() {
            const resource = currentResource();
            const postBody = resourceInput.value === 'login'
                ? {
                    username: usernameInput.value || 'casmidi',
                    password: passwordInput.value || ''
                }
                : (resource.post || {});

            return {
                empty: '',
                post: JSON.stringify(postBody, null, 2),
                put: JSON.stringify(resource.put || {}, null, 2)
            };
        }

        function updateRequestUrl() {
            if (resourceInput.value === 'custom') {
                return;
            }

            const resource = currentResource();
            const needsKey = methodInput.value === 'PUT' || methodInput.value === 'DELETE';
            urlInput.value = needsKey && resource.key
                ? resource.baseUrl + '/' + encodeURIComponent(resource.key)
                : resource.baseUrl;
        }

        function updateResourceDefaults() {
            if (resourceInput.value === 'custom') {
                return;
            }

            const resource = currentResource();

            if (resource.method) {
                methodInput.value = resource.method;
            }

            if (resource.authType) {
                authTypeInput.value = resource.authType;
            }

            authTypeInput.disabled = resourceInput.value === 'login';
        }

        function activeExampleName() {
            const activeTab = document.querySelector('.api-tester-tab.active');

            return activeTab ? activeTab.dataset.example : 'empty';
        }

        function setActiveExample(name) {
            tabs.forEach(function (item) {
                item.classList.toggle('active', item.dataset.example === name);
            });
        }

        function applyActiveExample() {
            const examples = bodyExamples();
            bodyInput.value = examples[activeExampleName()] || '';
        }

        function isLoginRequest() {
            return resourceInput.value === 'login' || buildUrl(urlInput.value) === '/api/v1/login';
        }

        function setOutput(message, isError = false) {
            responseOutput.textContent = message;
            statusOutput.textContent = isError ? 'Error' : '';
            statusOutput.className = 'api-tester-status' + (isError ? ' error' : '');
            timeOutput.textContent = '';
        }

        function parseJsonTextarea(text, fallback) {
            const trimmed = text.trim();

            if (trimmed === '') {
                return fallback;
            }

            return JSON.parse(trimmed);
        }

        function buildUrl(value) {
            const trimmed = value.trim();

            if (/^https?:\/\//i.test(trimmed)) {
                return trimmed;
            }

            return trimmed.startsWith('/') ? trimmed : '/' + trimmed;
        }

        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                tabs.forEach(function (item) {
                    item.classList.remove('active');
                });

                tab.classList.add('active');
                applyActiveExample();
            });
        });

        resourceInput.addEventListener('change', function () {
            updateResourceDefaults();
            updateRequestUrl();
            setActiveExample(resourceInput.value === 'login' ? 'post' : activeExampleName());
            applyActiveExample();
        });

        urlInput.addEventListener('input', function () {
            resourceInput.value = 'custom';
            authTypeInput.disabled = false;
        });

        methodInput.addEventListener('change', function () {
            updateRequestUrl();

            if (methodInput.value === 'GET' || methodInput.value === 'DELETE') {
                bodyInput.value = '';
                return;
            }

            applyActiveExample();
        });

        updateResourceDefaults();
        updateRequestUrl();
        setActiveExample(resourceInput.value === 'login' ? 'post' : activeExampleName());
        applyActiveExample();

        sendButton.addEventListener('click', async function () {
            const method = methodInput.value;
            const startedAt = performance.now();
            let headers = {};

            sendButton.disabled = true;
            sendButton.textContent = 'Sending...';
            statusOutput.textContent = '';
            timeOutput.textContent = '';
            responseOutput.textContent = 'Sending request...';

            try {
                headers = parseJsonTextarea(headersInput.value, {});

                if (isLoginRequest()) {
                    authTypeInput.value = 'none';
                }

                if (!isLoginRequest() && authTypeInput.value === 'bearer' && tokenInput.value.trim() !== '') {
                    headers.Authorization = 'Bearer ' + tokenInput.value.trim();
                }

                if (!isLoginRequest() && authTypeInput.value === 'basic') {
                    headers.Authorization = 'Basic ' + btoa(usernameInput.value + ':' + passwordInput.value);
                }

                const options = {
                    method,
                    headers
                };

                if (isLoginRequest()) {
                    const loginBody = bodyInput.value.trim() !== ''
                        ? parseJsonTextarea(bodyInput.value, {})
                        : {};

                    options.body = JSON.stringify({
                        username: String(loginBody.username || usernameInput.value || '').trim(),
                        password: String(loginBody.password || passwordInput.value || '')
                    });
                } else if (!['GET', 'DELETE'].includes(method)) {
                    const body = bodyInput.value.trim();

                    if (body !== '') {
                        JSON.parse(body);
                        options.body = body;
                    }
                }

                const response = await fetch(buildUrl(urlInput.value), options);
                const elapsed = Math.round(performance.now() - startedAt);
                const contentType = response.headers.get('content-type') || '';
                const payload = contentType.includes('application/json')
                    ? JSON.stringify(await response.json(), null, 2)
                    : await response.text();

                statusOutput.textContent = response.status + ' ' + response.statusText;
                statusOutput.className = 'api-tester-status ' + (response.ok ? 'ok' : 'error');
                timeOutput.textContent = elapsed + ' ms';
                responseOutput.textContent = payload || '(empty response)';
            } catch (error) {
                setOutput(error.message || String(error), true);
            } finally {
                sendButton.disabled = false;
                sendButton.textContent = 'Send';
            }
        });
    });
</script>
@endsection
