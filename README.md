

# 🌐 **Magento 2 Module: S3 DigitalOcean Space Storage**  
-----------------------------------------------

### 📦 **Vendor Name:** Ocode  
### 🛠️ **Module Name:** S3Digital

### 🔎 **Overview**

**S3Digital - DigitalOcean Spaces**: Scalable Cloud Storage for Magento 2

DigitalOcean Spaces provides reliable cloud storage for web apps, enabling seamless image storage without relying on local file systems. With cloud integration, businesses can scale effortlessly and enhance performance by offloading image retrieval to Spaces. This also supports CDN integration for faster delivery.

The **DigitalOcean Spaces Storage Extension** for Magento 2 stores product images, media files, and WYSIWYG assets directly in a Spaces bucket.  
This extension overrides Magento's default storage system to upload assets directly to **DigitalOcean Spaces**, ensuring compatibility with third-party extensions using Magento’s file system APIs.

---

## ⚙️ **Features**

### ✅ **Easy to Use**  
Minimal configuration required!  
1. Create a backup of your images.  
2. Follow simple steps to set up. You're good to go!  

### 🖼️ **Sync All Your Media Images**  
These images are automatically stored in **DigitalOcean Spaces**:  

- 📸 **Product images**  
- 🖼️ **Generated thumbnails**  
- ✍️ **WYSIWYG images**  
- 🏷️ **Category images**  
- 🔒 **CAPTCHA images**  
- 🖥️ **Logos and favicons**  

![](https://magentomedia.nyc3.cdn.digitaloceanspaces.com/Screenshot%20from%202025-02-13%2013-12-33.png)

---

## 📦 **Module Installation Steps:**

### **Step 1**: Install the Module  
Run this command in the **Magento 2 root directory**:

```bash
composer require ocode/s3digital
```

### **Step 2**: Enable the Module  
```bash
php bin/magento module:enable Ocode_S3Digital
```

### **Step 3**: Run Setup Upgrade Command  
```bash
php bin/magento setup:upgrade
```

### **Step 4**: Compile Dependencies (Production Mode)  
```bash
php bin/magento setup:di:compile
```

### **Step 5**: Deploy Static Content (if needed)  
```bash
php bin/magento setup:static-content:deploy
```

### **Step 6**: Clear the Cache  
```bash
php bin/magento cache:flush
```

---

## ⚙️ **Module Configuration in Magento Admin**

After installation, configure the module in the Magento Admin Panel:  

Navigate to:  
**Stores → Configuration → Ocode → DigitalOcean Space**

Enable the extension and enter the following credentials to connect:  
- 🛂 **Access Key**  
- 🔑 **Secret Key**  
- 🗂️ **Bucket Name**  
- 🌐 **Endpoint URL** (provided by DigitalOcean Spaces)  

Save the configuration and use the **"Check Connection"** button to verify. If credentials are correct, you'll see a success message.

---

## 🖱️ **Syncing Media to DigitalOcean Spaces**

### **Step 1**: Export Media Files  
Run the following command to sync all media files to DigitalOcean Spaces:  
```bash
php bin/magento s3digital:export
```

### **Step 2**: Enable DigitalOcean Spaces Storage  
Once the export is done, set DigitalOcean Spaces as the media storage:  
```bash
php bin/magento s3digital:enable
```

### **Step 3**: Disable DigitalOcean Spaces (if needed)  
If you need to switch back to local storage:  
```bash
php bin/magento s3digital:disable
```

---

## 🖥️ **Final Step: Configure Media URLs in Magento**

Update the **Secure** and **Unsecure** Media URLs in the **Magento Admin Panel** to load media from DigitalOcean Spaces:

**Navigate to:**  
**Stores → Configuration → Web → Base URLs**  

Set the following fields to your DigitalOcean CDN URL:  
- **Base URL for User Media Files:** `https://your-cdn-url/`  
- **Base URL for User Media Files (Secure):** `https://your-cdn-url/`  

Save the configuration and clear the cache:  
```bash
php bin/magento cache:flush
```

---

## 🎯 **Conclusion**

This module allows Magento 2 to offload media storage to **DigitalOcean Spaces**, improving performance, scalability, and load times by leveraging a CDN. 

Let me know if you need any other tweaks! 😊