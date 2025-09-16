<script type="text/javascript">
        var gk_isXlsx = false;
        var gk_xlsxFileLookup = {};
        var gk_fileData = {};
        function filledCell(cell) {
          return cell !== '' && cell != null;
        }
        function loadFileData(filename) {
        if (gk_isXlsx && gk_xlsxFileLookup[filename]) {
            try {
                var workbook = XLSX.read(gk_fileData[filename], { type: 'base64' });
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];

                // Convert sheet to JSON to filter blank rows
                var jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, blankrows: false, defval: '' });
                // Filter out blank rows (rows where all cells are empty, null, or undefined)
                var filteredData = jsonData.filter(row => row.some(filledCell));

                // Heuristic to find the header row by ignoring rows with fewer filled cells than the next row
                var headerRowIndex = filteredData.findIndex((row, index) =>
                  row.filter(filledCell).length >= filteredData[index + 1]?.filter(filledCell).length
                );
                // Fallback
                if (headerRowIndex === -1 || headerRowIndex > 25) {
                  headerRowIndex = 0;
                }

                // Convert filtered JSON back to CSV
                var csv = XLSX.utils.aoa_to_sheet(filteredData.slice(headerRowIndex)); // Create a new sheet from filtered array of arrays
                csv = XLSX.utils.sheet_to_csv(csv, { header: 1 });
                return csv;
            } catch (e) {
                console.error(e);
                return "";
            }
        }
        return gk_fileData[filename] || "";
        }
        </script><!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OpenPAi Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--scrollbar-track, #1a1a1a);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--scrollbar-thumb, #4b5563);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--scrollbar-thumb-hover, #6b7280);
        }
        /* Smooth transitions */
        .transition-all {
            transition: all 0.3s ease-in-out;
        }
        /* Custom accent color */
        .accent-color {
            background-color: var(--accent-color, #2563eb);
        }
        .accent-color-hover:hover {
            background-color: var(--accent-hover, #1e40af);
        }
        .ring-accent-color:focus {
            --tw-ring-color: var(--accent-color, #2563eb);
        }
        /* Light mode variables */
        .light-mode {
            --bg-primary: #f3f4f6;
            --bg-secondary: #ffffff;
            --bg-message: #e5e7eb;
            --text-primary: #1f2937;
            --text-secondary: #4b5563;
            --scrollbar-track: #e5e7eb;
            --scrollbar-thumb: #9ca3af;
            --scrollbar-thumb-hover: #6b7280;
        }
        /* Dark mode variables */
        .dark-mode {
            --bg-primary: #111827;
            --bg-secondary: #1f2937;
            --bg-message: #374151;
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --scrollbar-track: #1a1a1a;
            --scrollbar-thumb: #4b5563;
            --scrollbar-thumb-hover: #6b7280;
        }
        /* Code block styling */
        pre {
            background-color: var(--bg-message);
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
        }
        code {
            font-family: monospace;
        }
    </style>
</head>
<body class="bg-[var(--bg-primary)] text-[var(--text-primary)] font-sans antialiased dark-mode">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <div class="w-64 bg-[var(--bg-secondary)] p-4 flex flex-col transition-all">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">OP</div>
                    <h1 class="text-xl font-bold" data-i18n="chat_title">OpenPAi</h1>
                </div>
                <button class="text-[var(--text-secondary)] hover:text-[var(--text-primary)]" onclick="toggleSidebar()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <button class="accent-color text-white px-4 py-2 rounded-lg mb-4 accent-color-hover transition-all flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span data-i18n="new_chat">New Chat</span>
            </button>
            <div class="flex-1 overflow-y-auto">
                <h2 class="text-sm font-semibold text-[var(--text-secondary)] mb-2" data-i18n="history">History</h2>
                <ul class="space-y-2">
                    <li class="text-[var(--text-secondary)] hover:bg-gray-600 hover:bg-opacity-50 p-2 rounded-lg cursor-pointer transition-all">
                        Chat with Sonoma-Sky-Alpha
                    </li>
                    <li class="text-[var(--text-secondary)] hover:bg-gray-600 hover:bg-opacity-50 p-2 rounded-lg cursor-pointer transition-all">
                        Qwen Plus Discussion
                    </li>
                </ul>
            </div>
        </div>

        <!-- Main Chat Area -->
        <div class="flex-1 flex flex-col">
            <!-- Chat Header -->
            <div class="bg-[var(--bg-secondary)] p-4 flex justify-between items-center">
                <h2 class="text-lg font-semibold" data-i18n="new_chat">New Chat</h2>
                <div class="flex gap-2">
                    <button class="text-[var(--text-secondary)] hover:text-[var(--text-primary)]" onclick="toggleTheme()">
                        <svg id="themeIcon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>
                    <button class="text-[var(--text-secondary)] hover:text-[var(--text-primary)]" onclick="openSettings()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Chat Messages -->
            <div id="chatMessages" class="flex-1 p-6 overflow-y-auto">
                <div class="max-w-3xl mx-auto space-y-4">
                    <div class="bg-[var(--bg-message)] p-4 rounded-lg">
                        <p class="text-[var(--text-secondary)]" data-i18n="welcome_message">Hello! Welcome to the chat. Open the settings to get started.</p>
                    </div>
                </div>
            </div>

            <!-- Chat Input -->
            <div class="bg-[var(--bg-secondary)] p-4">
                <div class="max-w-3xl mx-auto flex gap-2">
                    <input id="chatInput" type="text" class="flex-1 bg-[var(--bg-secondary)] text-[var(--text-primary)] p-3 rounded-lg focus:outline-none focus:ring-2 ring-accent-color transition-all" placeholder="Type your message..." data-i18n-placeholder="type_message">
                    <button id="sendButton" class="accent-color text-white px-4 py-2 rounded-lg accent-color-hover transition-all flex items-center gap-2" onclick="sendMessage()">
                        <span id="sendText" data-i18n="send">Send</span>
                        <svg id="loadingSpinner" class="w-5 h-5 animate-spin hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Settings Modal -->
        <div id="settingsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
            <div class="bg-[var(--bg-secondary)] p-6 rounded-lg w-full max-w-md">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold" data-i18n="settings">Settings</h2>
                    <button class="text-[var(--text-secondary)] hover:text-[var(--text-primary)]" onclick="closeSettings()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-[var(--text-secondary)]" data-i18n="api_key">OpenRouter API Key</label>
                        <input id="apiKey" type="text" class="w-full bg-[var(--bg-secondary)] text-[var(--text-primary)] p-2 rounded-lg focus:outline-none focus:ring-2 ring-accent-color transition-all" placeholder="Enter API Key" data-i18n-placeholder="enter_api_key">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[var(--text-secondary)]" data-i18n="select_model">Select Model</label>
                        <select id="modelSelect" class="w-full bg-[var(--bg-secondary)] text-[var(--text-primary)] p-2 rounded-lg focus:outline-none focus:ring-2 ring-accent-color transition-all">
                            <option>Sonoma-Sky-Alpha</option>
                            <option>Qwen Plus 0728 (1M context)</option>
                            <option>DeepSeek-V3.1 (128K context)</option>
                            <option>GPT-4o Audio (128K context)</option>
                            <option>GPT-5 (400K context)</option>
                            <option>LongCat Flash Chat (128K context)</option>
                            <option>Mixtral 8x7B (32K context)</option>
                            <option>Llama 3.1 70B (128K context)</option>
                            <option>Claude 3.5 Sonnet (200K context)</option>
                            <option>Grok 3 (128K context)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[var(--text-secondary)]" data-i18n="select_language">Select Language</label>
                        <select id="languageSelect" class="w-full bg-[var(--bg-secondary)] text-[var(--text-primary)] p-2 rounded-lg focus:outline-none focus:ring-2 ring-accent-color transition-all" onchange="changeLanguage()">
                            <option value="en">English</option>
                            <option value="ar">العربية</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[var(--text-secondary)]" data-i18n="select_theme">Select Theme</label>
                        <select id="themeSelect" class="w-full bg-[var(--bg-secondary)] text-[var(--text-primary)] p-2 rounded-lg focus:outline-none focus:ring-2 ring-accent-color transition-all" onchange="toggleTheme()">
                            <option value="dark">Dark</option>
                            <option value="light">Light</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-[var(--text-secondary)]" data-i18n="custom_color">Custom Color</label>
                        <input type="color" id="customColor" class="w-full bg-[var(--bg-secondary)] p-1 rounded-lg" value="#2563eb">
                    </div>
                    <div class="flex gap-2">
                        <button class="w-full accent-color text-white p-2 rounded-lg accent-color-hover transition-all flex items-center justify-center gap-2" onclick="saveSettings()">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span data-i18n="save">Save</span>
                        </button>
                        <button class="w-full bg-red-600 text-white p-2 rounded-lg hover:bg-red-700 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            <span data-i18n="clear_history">Clear All History</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Language translations
        const translations = {
            en: {
                chat_title: "OpenPAi",
                new_chat: "New Chat",
                history: "History",
                settings: "Settings",
                api_key: "OpenRouter API Key",
                select_model: "Select Model",
                select_language: "Select Language",
                select_theme: "Select Theme",
                custom_color: "Custom Color",
                save: "Save",
                clear_history: "Clear All History",
                welcome_message: "Hello! Welcome to the chat. Open the settings to get started.",
                type_message: "Type your message...",
                enter_api_key: "Enter API Key",
                send: "Send"
            },
            ar: {
                chat_title: "OpenPAi",
                new_chat: "دردشة جديدة",
                history: "السجل",
                settings: "الإعدادات",
                api_key: "مفتاح OpenRouter API",
                select_model: "اختر النموذج",
                select_language: "اختر اللغة",
                select_theme: "اختر الثيم",
                custom_color: "لون مخصص",
                save: "حفظ",
                clear_history: "مسح كل السجل",
                welcome_message: "مرحبًا! مرحبًا بك في الدردشة. افتح الإعدادات للبدء.",
                type_message: "اكتب رسالتك...",
                enter_api_key: "أدخل مفتاح API",
                send: "إرسال"
            }
        };

        // Chat history
        let chatHistory = [];

        function toggleSidebar() {
            document.querySelector('.w-64').classList.toggle('hidden');
        }

        function openSettings() {
            document.getElementById('settingsModal').classList.remove('hidden');
        }

        function closeSettings() {
            document.getElementById('settingsModal').classList.add('hidden');
        }

        function toggleTheme() {
            const theme = document.getElementById('themeSelect').value;
            const body = document.body;
            const themeIcon = document.getElementById('themeIcon');
            if (theme === 'light') {
                body.classList.remove('dark-mode');
                body.classList.add('light-mode');
                themeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />`;
            } else {
                body.classList.remove('light-mode');
                body.classList.add('dark-mode');
                themeIcon.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />`;
            }
        }

        function saveSettings() {
            const color = document.getElementById('customColor').value;
            document.documentElement.style.setProperty('--accent-color', color);
            document.documentElement.style.setProperty('--accent-hover', darkenColor(color));
            toggleTheme();
            closeSettings();
        }

        function darkenColor(hex) {
            let r = parseInt(hex.slice(1, 3), 16);
            let g = parseInt(hex.slice(3, 5), 16);
            let b = parseInt(hex.slice(5, 7), 16);
            r = Math.max(0, r - 50);
            g = Math.max(0, g - 50);
            b = Math.max(0, b - 50);
            return `rgb(${r}, ${g}, ${b})`;
        }

        function changeLanguage() {
            const lang = document.getElementById('languageSelect').value;
            const elements = document.querySelectorAll('[data-i18n]');
            const placeholders = document.querySelectorAll('[data-i18n-placeholder]');
            
            elements.forEach(element => {
                const key = element.getAttribute('data-i18n');
                element.textContent = translations[lang][key] || element.textContent;
            });
            
            placeholders.forEach(element => {
                const key = element.getAttribute('data-i18n-placeholder');
                element.placeholder = translations[lang][key] || element.placeholder;
            });
            
            document.documentElement.setAttribute('dir', lang === 'ar' ? 'rtl' : 'ltr');
            document.querySelector('html').setAttribute('lang', lang);
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        async function sendMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            if (!message) return;

            const apiKey = document.getElementById('apiKey').value.trim();
            if (!apiKey) {
                alert('Please enter your OpenRouter API Key in settings.');
                return;
            }

            const model = document.getElementById('modelSelect').value;

            // Show user message
            appendMessage('user', message);

            // Clear input
            input.value = '';

            // Show loading
            const sendButton = document.getElementById('sendButton');
            const sendText = document.getElementById('sendText');
            const loadingSpinner = document.getElementById('loadingSpinner');
            sendButton.disabled = true;
            sendText.classList.add('hidden');
            loadingSpinner.classList.remove('hidden');

            // Update chat history
            chatHistory.push({ role: 'user', content: message });

            try {
                const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${apiKey}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        model: model,
                        messages: chatHistory
                    })
                });

                if (!response.ok) {
                    throw new Error('API request failed');
                }

                const data = await response.json();
                const aiMessage = data.choices[0].message.content;

                // Append AI message
                appendMessage('ai', aiMessage);

                // Update chat history
                chatHistory.push({ role: 'assistant', content: aiMessage });
            } catch (error) {
                appendMessage('ai', 'Error: Failed to get response.');
            } finally {
                // Hide loading
                sendButton.disabled = false;
                sendText.classList.remove('hidden');
                loadingSpinner.classList.add('hidden');
            }
        }

        function appendMessage(sender, content) {
            const messagesContainer = document.getElementById('chatMessages').querySelector('.max-w-3xl');
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('p-4', 'rounded-lg', 'mb-4');
            if (sender === 'user') {
                messageDiv.classList.add('bg-blue-600', 'text-white', 'ml-auto');
            } else {
                messageDiv.classList.add('bg-[var(--bg-message)]', 'text-[var(--text-primary)]');
            }

            // Process content for code blocks
            const processedContent = processContent(content);
            messageDiv.innerHTML = processedContent;

            messagesContainer.appendChild(messageDiv);

            // Scroll to bottom
            document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight;
        }

        function processContent(content) {
            // Escape HTML
            let escaped = escapeHtml(content);

            // Detect code blocks and wrap in pre/code
            escaped = escaped.replace(/```([\s\S]*?)```/g, (match, p1) => {
                return `<pre><code>${escapeHtml(p1.trim())}</code></pre>`;
            });

            // Convert newlines to <br>
            escaped = escaped.replace(/\n/g, '<br>');

            return escaped;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            changeLanguage();
            toggleTheme();

            // Enter key to send
            document.getElementById('chatInput').addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    sendMessage();
                }
            });
        });
    </script>
</body>
</html>
