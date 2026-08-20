// Cryptographic operations using Web Crypto API
// Focuses on AES-256-GCM and PBKDF2 for Master Key derivation

export const CryptoManager = {
    // Basic array buffer to hex conversion (for hashes)
    bufferToHex(buffer) {
        const hashArray = Array.from(new Uint8Array(buffer));
        return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
    },
    
    // Hex to Uint8Array
    hexToBuffer(hex) {
        const view = new Uint8Array(hex.length / 2);
        for (let i = 0; i < hex.length; i += 2) {
            view[i / 2] = parseInt(hex.substring(i, i + 2), 16);
        }
        return view.buffer;
    },

    // Convert string to ArrayBuffer
    str2ab(str) {
        return new TextEncoder().encode(str);
    },

    // Compute SHA-256 hash of a file/buffer
    async computeHash(arrayBuffer) {
        const hashBuffer = await window.crypto.subtle.digest('SHA-256', arrayBuffer);
        return this.bufferToHex(hashBuffer);
    },

    // Derive a 256-bit AES-GCM Master Key from a password using PBKDF2
    async deriveMasterKey(password, saltString = 'FMSS_MASTER_SALT') {
        const enc = new TextEncoder();
        const keyMaterial = await window.crypto.subtle.importKey(
            "raw",
            enc.encode(password),
            { name: "PBKDF2" },
            false,
            ["deriveBits", "deriveKey"]
        );

        return await window.crypto.subtle.deriveKey(
            {
                name: "PBKDF2",
                salt: enc.encode(saltString),
                iterations: 250000,
                hash: "SHA-256"
            },
            keyMaterial,
            { name: "AES-GCM", length: 256 },
            true, // extractable so we can wrap/unwrap
            ["wrapKey", "unwrapKey", "encrypt", "decrypt"]
        );
    },

    // Generate a random AES-256-GCM Data Key
    async generateDataKey() {
        return await window.crypto.subtle.generateKey(
            { name: "AES-GCM", length: 256 },
            true, // extractable
            ["encrypt", "decrypt"]
        );
    },

    // Wrap a Data Key using the Master Key
    async wrapKey(dataKey, masterKey) {
        const iv = window.crypto.getRandomValues(new Uint8Array(12));
        const wrappedKeyBuffer = await window.crypto.subtle.wrapKey(
            "raw",
            dataKey,
            masterKey,
            { name: "AES-GCM", iv: iv }
        );
        
        // Return both the wrapped key and the IV used to wrap it
        return {
            wrappedKey: this.bufferToHex(wrappedKeyBuffer),
            wrapIv: this.bufferToHex(iv)
        };
    },

    // Unwrap a Data Key using the Master Key
    async unwrapKey(wrappedKeyHex, wrapIvHex, masterKey) {
        const wrappedKeyBuffer = this.hexToBuffer(wrappedKeyHex);
        const ivBuffer = this.hexToBuffer(wrapIvHex);
        
        return await window.crypto.subtle.unwrapKey(
            "raw",
            wrappedKeyBuffer,
            masterKey,
            { name: "AES-GCM", iv: ivBuffer },
            { name: "AES-GCM", length: 256 },
            true,
            ["encrypt", "decrypt"]
        );
    },

    // Export a raw CryptoKey to Hex (for Link Sharing)
    async exportKeyToHex(cryptoKey) {
        const exported = await window.crypto.subtle.exportKey("raw", cryptoKey);
        return this.bufferToHex(exported);
    },

    // Import a Hex key back to a raw CryptoKey (for Link Sharing Decrypt)
    async importKeyFromHex(hexString) {
        const buffer = this.hexToBuffer(hexString);
        return await window.crypto.subtle.importKey(
            "raw",
            buffer,
            { name: "AES-GCM", length: 256 },
            true,
            ["encrypt", "decrypt"]
        );
    },

    // Encrypt a file buffer
    async encryptFile(fileArrayBuffer, masterKey) {
        const dataKey = await this.generateDataKey();
        const iv = window.crypto.getRandomValues(new Uint8Array(12));
        
        const ciphertextBuffer = await window.crypto.subtle.encrypt(
            { name: "AES-GCM", iv: iv },
            dataKey,
            fileArrayBuffer
        );
        
        const fileHash = await this.computeHash(fileArrayBuffer);
        const { wrappedKey, wrapIv } = await this.wrapKey(dataKey, masterKey);
        
        return {
            ciphertext: new Blob([ciphertextBuffer], { type: "application/octet-stream" }),
            iv: this.bufferToHex(iv),
            wrappedDataKey: wrapIv + ':' + wrappedKey,
            fileHash: fileHash
        };
    },

    // Decrypt a file buffer
    async decryptFile(ciphertextBuffer, ivHex, wrappedKeyCombined, masterKey) {
        const parts = wrappedKeyCombined.split(':');
        let dataKey;
        if (parts.length === 2) {
            dataKey = await this.unwrapKey(parts[1], parts[0], masterKey);
        } else {
            // fallback if stored differently
            dataKey = await this.unwrapKey(wrappedKeyCombined, "000000000000000000000000", masterKey);
        }
        
        const ivBuffer = this.hexToBuffer(ivHex);
        
        const plaintextBuffer = await window.crypto.subtle.decrypt(
            { name: "AES-GCM", iv: ivBuffer },
            dataKey,
            ciphertextBuffer
        );
        
        return plaintextBuffer;
    },

    // Encrypt a string (e.g. JSON payload for logins)
    async encryptString(text, masterKey) {
        const textBuffer = this.str2ab(text);
        const dataKey = await this.generateDataKey();
        const iv = window.crypto.getRandomValues(new Uint8Array(12));
        
        const ciphertextBuffer = await window.crypto.subtle.encrypt(
            { name: "AES-GCM", iv: iv },
            dataKey,
            textBuffer
        );
        
        const { wrappedKey, wrapIv } = await this.wrapKey(dataKey, masterKey);
        
        return {
            ciphertextHex: this.bufferToHex(ciphertextBuffer),
            iv: this.bufferToHex(iv),
            wrappedDataKey: wrapIv + ':' + wrappedKey
        };
    },

    // Decrypt a string (e.g. JSON payload for logins)
    async decryptString(ciphertextHex, ivHex, wrappedKeyCombined, masterKey) {
        const parts = wrappedKeyCombined.split(':');
        let dataKey;
        if (parts.length === 2) {
            dataKey = await this.unwrapKey(parts[1], parts[0], masterKey);
        } else {
            dataKey = await this.unwrapKey(wrappedKeyCombined, "000000000000000000000000", masterKey);
        }
        
        const ivBuffer = this.hexToBuffer(ivHex);
        const ciphertextBuffer = this.hexToBuffer(ciphertextHex);
        
        const plaintextBuffer = await window.crypto.subtle.decrypt(
            { name: "AES-GCM", iv: ivBuffer },
            dataKey,
            ciphertextBuffer
        );
        
        return new TextDecoder().decode(plaintextBuffer);
    }
};
