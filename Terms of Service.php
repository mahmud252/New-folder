<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Translation arrays
$translations = [
    'en' => [
        'title' => 'Terms of Service - Secure Media Storage',
        'last_updated' => 'Last Updated',
        'acceptance' => '1. Acceptance of Terms',
        'acceptance_text' => 'By accessing or using Secure Media Storage ("Service"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree to all terms, please do not use the Service.',
        'service_desc' => '2. Service Description',
        'service_text' => 'Secure Media Storage provides a private cloud storage solution for personal media with these key features:',
        'private_storage' => 'Private Media Storage',
        'private_text' => 'A secure platform to store personal photos and videos with these advantages:',
        'private_access' => 'Private access: View your media anytime while maintaining complete privacy',
        'unlimited_storage' => 'Unlimited storage: Store all your important memories without capacity restrictions',
        'cross_device' => 'Cross-device access: Available on mobile, tablet, and computer with automatic sync',
        'auto_backup' => 'Automatic backup: Cloud storage protects against accidental deletion',
        'privacy_protect' => 'Privacy protection: Optional password or biometric lock for specific albums',
        'full_quality' => 'Full quality: Original resolution preservation for all media',
        'controlled_share' => 'Controlled sharing: Option to share specific items via secure links',
        'ad_free' => 'Ad-free experience: No advertisements or hidden costs',
        'user_responsibilities' => '3. User Responsibilities',
        'user_resp1' => 'You are solely responsible for the content you upload and store',
        'user_resp2' => 'You must ensure you have all necessary rights to store the media',
        'user_resp3' => 'You must maintain the confidentiality of your account credentials',
        'user_resp4' => 'You agree not to use the Service for illegal or prohibited content',
        'prohibited_content' => '4. Prohibited Content',
        'prohibited_text' => 'You may not store or share content that:',
        'prohibited1' => 'Violates any laws or regulations',
        'prohibited2' => 'Infringes on intellectual property rights',
        'prohibited3' => 'Contains malware or harmful code',
        'prohibited4' => 'Is pornographic or sexually explicit',
        'prohibited5' => 'Promotes violence, hate speech, or discrimination',
        'privacy_security' => '5. Privacy and Security',
        'privacy_text' => 'We implement industry-standard security measures including:',
        'privacy1' => 'End-to-end encryption for file transfers',
        'privacy2' => 'Secure server infrastructure',
        'privacy3' => 'Regular security audits',
        'privacy_note' => 'However, no system is completely secure - you should maintain your own backups of critical files.',
        'account_termination' => '6. Account Termination',
        'account_text' => 'We reserve the right to:',
        'account1' => 'Suspend or terminate accounts violating these Terms',
        'account2' => 'Remove content that violates our policies',
        'account3' => 'Discontinue service with 30 days notice',
        'warranty' => '7. Disclaimer of Warranties',
        'warranty_text' => 'The Service is provided "as is" without warranties of:',
        'warranty1' => 'Continuous or error-free operation',
        'warranty2' => 'Complete data protection or recovery',
        'warranty3' => 'Fitness for any particular purpose',
        'liability' => '8. Limitation of Liability',
        'liability_text' => 'We shall not be liable for:',
        'liability1' => 'Any loss or corruption of data',
        'liability2' => 'Unauthorized access to accounts',
        'liability3' => 'Interruptions or delays in service',
        'liability4' => 'Consequential or incidental damages',
        'changes' => '9. Changes to Terms',
        'changes_text' => 'We may modify these Terms at any time. Continued use after changes constitutes acceptance.',
       
    ],
    'bn' => [
        'title' => 'সেবার শর্তাবলী - সুরক্ষিত মিডিয়া স্টোরেজ',
        'last_updated' => 'সর্বশেষ আপডেট',
        'acceptance' => '১. শর্তাবলী গ্রহণ',
        'acceptance_text' => 'সিকিউর মিডিয়া স্টোরেজ ("সেবা") ব্যবহার করার মাধ্যমে আপনি এই সেবার শর্তাবলী ("শর্ত") মেনে নিচ্ছেন। আপনি যদি সব শর্তে সম্মত না হন, অনুগ্রহ করে এই সেবা ব্যবহার করবেন না।',
        'service_desc' => '২. সেবা বর্ণনা',
        'service_text' => 'সিকিউর মিডিয়া স্টোরেজ ব্যক্তিগত মিডিয়ার জন্য একটি প্রাইভেট ক্লাউড স্টোরেজ সমাধান প্রদান করে যার প্রধান বৈশিষ্ট্যগুলো হলো:',
        'private_storage' => 'ব্যক্তিগত মিডিয়া স্টোরেজ',
        'private_text' => 'ব্যক্তিগত ছবি এবং ভিডিও সংরক্ষণের জন্য একটি সুরক্ষিত প্ল্যাটফর্ম যার সুবিধাগুলো হলো:',
        'private_access' => 'ব্যক্তিগত অ্যাক্সেস: সম্পূর্ণ গোপনীয়তা বজায় রেখে যেকোনো সময় আপনার মিডিয়া দেখুন',
        'unlimited_storage' => 'সীমাহীন স্টোরেজ: আপনার সমস্ত গুরুত্বপূর্ণ স্মৃতি ধারণক্ষমতার সীমাবদ্ধতা ছাড়াই সংরক্ষণ করুন',
        'cross_device' => 'ক্রস-ডিভাইস অ্যাক্সেস: মোবাইল, ট্যাবলেট এবং কম্পিউটারে স্বয়ংক্রিয় সিঙ্কের সাথে উপলব্ধ',
        'auto_backup' => 'স্বয়ংক্রিয় ব্যাকআপ: আকস্মিক মুছে ফেলার বিরুদ্ধে ক্লাউড স্টোরেজ সুরক্ষা প্রদান করে',
        'privacy_protect' => 'গোপনীয়তা সুরক্ষা: নির্দিষ্ট অ্যালবামের জন্য ঐচ্ছিক পাসওয়ার্ড বা বায়োমেট্রিক লক',
        'full_quality' => 'পূর্ণ গুণগত মান: সমস্ত মিডিয়ার জন্য আসল রেজোলিউশন সংরক্ষণ',
        'controlled_share' => 'নিয়ন্ত্রিত শেয়ারিং: নিরাপদ লিঙ্কের মাধ্যমে নির্দিষ্ট আইটেম শেয়ারের বিকল্প',
        'ad_free' => 'বিজ্ঞাপন-মুক্ত অভিজ্ঞতা: কোন বিজ্ঞাপন বা লুকানো খরচ নেই',
        'user_responsibilities' => '৩. ব্যবহারকারীর দায়িত্ব',
        'user_resp1' => 'আপনি আপলোড এবং সংরক্ষণ করা কন্টেন্টের জন্য একমাত্র দায়ী',
        'user_resp2' => 'আপনাকে নিশ্চিত করতে হবে যে মিডিয়া সংরক্ষণের জন্য আপনার সমস্ত প্রয়োজনীয় অধিকার রয়েছে',
        'user_resp3' => 'আপনাকে আপনার অ্যাকাউন্টের তথ্যের গোপনীয়তা বজায় রাখতে হবে',
        'user_resp4' => 'আপনি অবৈধ বা নিষিদ্ধ কন্টেন্টের জন্য এই সেবা ব্যবহার না করতে সম্মত হন',
        'prohibited_content' => '৪. নিষিদ্ধ কন্টেন্ট',
        'prohibited_text' => 'আপনি এমন কন্টেন্ট সংরক্ষণ বা শেয়ার করতে পারবেন না যা:',
        'prohibited1' => 'কোনো আইন বা নিয়ম লঙ্ঘন করে',
        'prohibited2' => 'মেধা সম্পত্তি অধিকার লঙ্ঘন করে',
        'prohibited3' => 'ম্যালওয়্যার বা ক্ষতিকারক কোড ধারণ করে',
        'prohibited4' => 'অশ্লীল বা যৌনভাবে স্পষ্ট',
        'prohibited5' => 'হিংসা, ঘৃণামূলক বক্তব্য বা বৈষম্যকে প্রচার করে',
        'privacy_security' => '৫. গোপনীয়তা ও নিরাপত্তা',
        'privacy_text' => 'আমরা শিল্প-মানের নিরাপত্তা ব্যবস্থা বাস্তবায়ন করি যার মধ্যে রয়েছে:',
        'privacy1' => 'ফাইল ট্রান্সফারের জন্য এন্ড-টু-এন্ড এনক্রিপশন',
        'privacy2' => 'সুরক্ষিত সার্ভার অবকাঠামো',
        'privacy3' => 'নিয়মিত নিরাপত্তা অডিট',
        'privacy_note' => 'তবে, কোনো সিস্টেমই সম্পূর্ণ সুরক্ষিত নয় - আপনার গুরুত্বপূর্ণ ফাইলের নিজস্ব ব্যাকআপ বজায় রাখা উচিত।',
        'account_termination' => '৬. অ্যাকাউন্ট সমাপ্তি',
        'account_text' => 'আমরা অধিকার সংরক্ষণ করি:',
        'account1' => 'এই শর্তাবলী লঙ্ঘনকারী অ্যাকাউন্ট স্থগিত বা সমাপ্ত করতে',
        'account2' => 'আমাদের নীতিমালা লঙ্ঘন করে এমন কন্টেন্ট সরাতে',
        'account3' => '৩০ দিনের নোটিশে সেবা বন্ধ করতে',
        'warranty' => '৭. ওয়ারেন্টি অস্বীকার',
        'warranty_text' => 'সেবা "যেমন আছে" ভিত্তিতে প্রদান করা হয় এবং এর জন্য কোনো ওয়ারেন্টি নেই:',
        'warranty1' => 'অবিচ্ছিন্ন বা ত্রুটিমুক্ত অপারেশন',
        'warranty2' => 'সম্পূর্ণ ডেটা সুরক্ষা বা পুনরুদ্ধার',
        'warranty3' => 'কোনো নির্দিষ্ট উদ্দেশ্যের জন্য উপযুক্ততা',
        'liability' => '৮. দায় সীমাবদ্ধতা',
        'liability_text' => 'আমরা দায়ী থাকব না:',
        'liability1' => 'কোনো ডেটা হারানো বা ক্ষতির জন্য',
        'liability2' => 'অননুমোদিত অ্যাকাউন্ট অ্যাক্সেসের জন্য',
        'liability3' => 'সেবায় বাধা বা বিলম্বের জন্য',
        'liability4' => 'পরোক্ষ বা আকস্মিক ক্ষতির জন্য',
        'changes' => '৯. শর্তাবলীতে পরিবর্তন',
        'changes_text' => 'আমরা যেকোনো সময় এই শর্তাবলী পরিবর্তন করতে পারি। পরিবর্তনের পর সেবা ব্যবহার অব্যাহত রাখা পরিবর্তনগুলি গ্রহণ করার সমতুল্য।',
        'contact' => '১০. যোগাযোগের তথ্য',
        'contact_text' => 'এই শর্তাবলী সম্পর্কে প্রশ্নের জন্য, অনুগ্রহ করে যোগাযোগ করুন:',
        'email' => 'ইমেইল: legal@securemediastorage.example',
        'address' => 'ঠিকানা: ১২৩ লিগ্যাল স্ট্রিট, কমপ্লায়েন্স সিটি, সিসি ১২৩৪৫',
        'effective_date' => 'কার্যকর তারিখ'
    ],
    'hi' => [
        'title' => 'सेवा की शर्तें - सुरक्षित मीडिया भंडारण',
        'last_updated' => 'अंतिम अपडेट',
        'acceptance' => '1. शर्तों की स्वीकृति',
        'acceptance_text' => 'सिक्योर मीडिया स्टोरेज ("सेवा") का उपयोग करके आप इन सेवा की शर्तों ("शर्तें") से बंधे होते हैं। यदि आप सभी शर्तों से सहमत नहीं हैं, तो कृपया इस सेवा का उपयोग न करें।',
        'service_desc' => '2. सेवा विवरण',
        'service_text' => 'सिक्योर मीडिया स्टोरेज निजी मीडिया के लिए एक क्लाउड भंडारण समाधान प्रदान करता है जिसकी प्रमुख विशेषताएं हैं:',
        'private_storage' => 'निजी मीडिया भंडारण',
        'private_text' => 'निजी तस्वीरों और वीडियो को स्टोर करने के लिए एक सुरक्षित प्लेटफॉर्म जिसके लाभ हैं:',
        'private_access' => 'निजी पहुंच: पूर्ण गोपनीयता बनाए रखते हुए कभी भी अपने मीडिया देखें',
        'unlimited_storage' => 'असीमित भंडारण: क्षमता सीमा के बिना अपनी सभी महत्वपूर्ण यादें स्टोर करें',
        'cross_device' => 'क्रॉस-डिवाइस पहुंच: मोबाइल, टैबलेट और कंप्यूटर पर स्वचालित सिंक के साथ उपलब्ध',
        'auto_backup' => 'स्वचालित बैकअप: आकस्मिक विलोपन से बचाने के लिए क्लाउड भंडारण',
        'privacy_protect' => 'गोपनीयता सुरक्षा: विशिष्ट एल्बम के लिए वैकल्पिक पासवर्ड या बायोमेट्रिक लॉक',
        'full_quality' => 'पूर्ण गुणवत्ता: सभी मीडिया के लिए मूल रिज़ॉल्यूशन संरक्षण',
        'controlled_share' => 'नियंत्रित साझाकरण: सुरक्षित लिंक के माध्यम से विशिष्ट आइटम साझा करने का विकल्प',
        'ad_free' => 'विज्ञापन-मुक्त अनुभव: कोई विज्ञापन या छिपी लागत नहीं',
        'user_responsibilities' => '3. उपयोगकर्ता जिम्मेदारियां',
        'user_resp1' => 'आप अपलोड और संग्रहीत सामग्री के लिए पूरी तरह से जिम्मेदार हैं',
        'user_resp2' => 'आपको यह सुनिश्चित करना होगा कि मीडिया संग्रहीत करने के लिए आपके पास सभी आवश्यक अधिकार हैं',
        'user_resp3' => 'आपको अपने खाते की साख की गोपनीयता बनाए रखनी होगी',
        'user_resp4' => 'आप अवैध या निषिद्ध सामग्री के लिए इस सेवा का उपयोग न करने के लिए सहमत हैं',
        'prohibited_content' => '4. निषिद्ध सामग्री',
        'prohibited_text' => 'आप ऐसी सामग्री स्टोर या साझा नहीं कर सकते जो:',
        'prohibited1' => 'किसी भी कानून या विनियम का उल्लंघन करती हो',
        'prohibited2' => 'बौद्धिक संपदा अधिकारों का उल्लंघन करती हो',
        'prohibited3' => 'मैलवेयर या हानिकारक कोड शामिल करती हो',
        'prohibited4' => 'अश्लील या यौन रूप से स्पष्ट हो',
        'prohibited5' => 'हिंसा, घृणा भाषण या भेदभाव को बढ़ावा देती हो',
        'privacy_security' => '5. गोपनीयता और सुरक्षा',
        'privacy_text' => 'हम उद्योग-मानक सुरक्षा उपायों को लागू करते हैं जिनमें शामिल हैं:',
        'privacy1' => 'फ़ाइल स्थानांतरण के लिए एंड-टू-एंड एन्क्रिप्शन',
        'privacy2' => 'सुरक्षित सर्वर बुनियादी ढांचा',
        'privacy3' => 'नियमित सुरक्षा ऑडिट',
        'privacy_note' => 'हालांकि, कोई भी सिस्टम पूरी तरह से सुरक्षित नहीं है - आपको महत्वपूर्ण फ़ाइलों का अपना बैकअप बनाए रखना चाहिए।',
        'account_termination' => '6. खाता समाप्ति',
        'account_text' => 'हम अधिकार सुरक्षित रखते हैं:',
        'account1' => 'इन शर्तों का उल्लंघन करने वाले खातों को निलंबित या समाप्त करने का',
        'account2' => 'हमारी नीतियों का उल्लंघन करने वाली सामग्री को हटाने का',
        'account3' => '30 दिनों के नोटिस पर सेवा बंद करने का',
        'warranty' => '7. वारंटी अस्वीकरण',
        'warranty_text' => 'सेवा "जैसा है" के आधार पर प्रदान की जाती है और इसके लिए कोई वारंटी नहीं है:',
        'warranty1' => 'निरंतर या त्रुटि-मुक्त संचालन',
        'warranty2' => 'पूर्ण डेटा सुरक्षा या पुनर्प्राप्ति',
        'warranty3' => 'किसी विशेष उद्देश्य के लिए उपयुक्तता',
        'liability' => '8. देयता सीमा',
        'liability_text' => 'हम जिम्मेदार नहीं होंगे:',
        'liability1' => 'किसी भी डेटा के नुकसान या भ्रष्टाचार के लिए',
        'liability2' => 'अनधिकृत खाता पहुंच के लिए',
        'liability3' => 'सेवा में व्यवधान या देरी के लिए',
        'liability4' => 'परिणामी या आकस्मिक क्षति के लिए',
        'changes' => '9. शर्तों में परिवर्तन',
        'changes_text' => 'हम किसी भी समय इन शर्तों को संशोधित कर सकते हैं। परिवर्तनों के बाद सेवा का उपयोग जारी रखना परिवर्तनों को स्वीकार करने के बराबर है।',
        'contact' => '10. संपर्क जानकारी',
        'contact_text' => 'इन शर्तों के बारे में प्रश्नों के लिए, कृपया संपर्क करें:',
        'email' => 'ईमेल: legal@securemediastorage.example',
        'address' => 'पता: 123 लीगल स्ट्रीट, कम्प्लायंस सिटी, सीसी 12345',
        'effective_date' => 'प्रभावी तिथि'
    ]
];

// Default language
$lang = isset($_GET['lang']) && array_key_exists($_GET['lang'], $translations) ? $_GET['lang'] : 'en';
$t = $translations[$lang];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $t['title']; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
    <style>
     :root {
    --primary-color: #4a6bff;
    --secondary-color: #3a5bef;
    --dark-color: #333;
    --light-color: #f8f9fa;
    --gray-color: #6c757d;
    --border-radius: 8px;
    --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;
    color: var(--dark-color);
    background-color: var(--light-color);
    padding: 20px;
}

@media (prefers-color-scheme: dark) {
    body {
        background-color: #1a1a1a;
        color:rgb(0, 0, 0);
    }
    
    .tos-container {
        background-color: #2d2d2d;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    }
    
    .tos-header {
        border-bottom: 1px solid #444;
    }
    
    .feature-highlight {
        background-color: #333;
        border-left-color: var(--primary-color);
    }
}

.tos-container {
    max-width: 900px;
    margin: 30px auto;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow);
    overflow: hidden;
    padding: 0;
}

.tos-header {
    padding: 30px;
    text-align: center;
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    border-bottom: 1px solid #eee;
}

.tos-header h1 {
    font-size: 2.2rem;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.tos-header p {
    font-size: 0.9rem;
    opacity: 0.9;
}

.tos-content {
    padding: 30px;
}

.tos-section {
    margin-bottom: 30px;
}

.tos-section h2 {
    font-size: 1.5rem;
    margin-bottom: 15px;
    color: var(--primary-color);
    padding-bottom: 5px;
    border-bottom: 2px solid #eee;
    display: flex;
    align-items: center;
    gap: 10px;
}

.tos-section h2 i {
    font-size: 1.2rem;
}

.tos-section p {
    margin-bottom: 15px;
    font-size: 1rem;
}

.tos-section ul {
    margin: 15px 0 15px 30px;
}

.tos-section li {
    margin-bottom: 8px;
    font-size: 0.95rem;
}

.feature-highlight {
    background-color: #f8f9fa;
    border-left: 4px solid var(--primary-color);
    padding: 20px;
    border-radius: var(--border-radius);
    margin: 20px 0;
}

.feature-highlight h3 {
    font-size: 1.2rem;
    margin-bottom: 15px;
    color: var(--primary-color);
    display: flex;
    align-items: center;
    gap: 10px;
}

.feature-highlight h3 i {
    font-size: 1rem;
}

.feature-highlight ul {
    margin: 15px 0 0 20px;
}

.feature-highlight li {
    margin-bottom: 10px;
    position: relative;
    padding-left: 15px;
}

.feature-highlight li:before {
    content: "•";
    color: var(--primary-color);
    position: absolute;
    left: 0;
    font-weight: bold;
}

.contact-info {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: var(--border-radius);
    margin-top: 30px;
}

.contact-info h2 {
    margin-top: 0;
}

.last-updated {
    text-align: center;
    margin-top: 30px;
    font-size: 0.9rem;
    color: var(--gray-color);
    font-style: italic;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .tos-container {
        margin: 15px;
    }
    
    .tos-header {
        padding: 20px;
    }
    
    .tos-header h1 {
        font-size: 1.8rem;
    }
    
    .tos-content {
        padding: 20px;
    }
    
    .tos-section h2 {
        font-size: 1.3rem;
    }
}

@media (max-width: 480px) {
    body {
        padding: 10px;
    }
    
    .tos-header h1 {
        font-size: 1.5rem;
        flex-direction: column;
        gap: 5px;
    }
    
    .tos-section h2 {
        font-size: 1.2rem;
    }
    
    .feature-highlight {
        padding: 15px;
    }
}
        .language-selector {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        .lang-btn {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            transition: all 0.3s ease;
        }
        
        .lang-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }
        
        .lang-options {
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            overflow: hidden;
            display: none;
            min-width: 120px;
        }
        
        .lang-options a {
            display: block;
            padding: 8px 15px;
            color: var(--dark-color);
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .lang-options a:hover {
            background: #f0f0f0;
            color: var(--primary-color);
        }
        
        .show-lang-options {
            display: block;
        }
        
        /* Dark mode for language selector */
        @media (prefers-color-scheme: dark) {
            .lang-options {
                background: #2d2d2d;
            }
            
            .lang-options a {
                color: #e0e0e0;
            }
            
            .lang-options a:hover {
                background: #3d3d3d;
            }
        }

        /* Footer */
        .footer {
            margin-top: 4rem;
            color: var(--medium-gray);
            font-size: 0.95rem;
            width: 100%;
            padding-top: 2rem;
            border-top: 1px solid var(--light-gray);
        }

        .footer p {
            margin-bottom: 0.5rem;
        }

        .footer-links {
            display: flex;
            gap: 1.5rem;
            justify-content: center;
            margin-top: 1rem;
        }

        .footer-links a {
            color: var(--medium-gray);
            text-decoration: none;
            transition: var(--transition);
        }

        .footer-links a:hover {
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Language Selector Button -->
    <div class="language-selector">
        <button class="lang-btn" onclick="toggleLanguageOptions()">
            <i class="fas fa-language"></i>
            <?php echo strtoupper($lang); ?>
        </button>
        <div class="lang-options" id="langOptions">
            <a href="?lang=en">English</a>
            <a href="?lang=bn">বাংলা (Bangla)</a>
            <a href="?lang=hi">हिन्दी (Hindi)</a>
        </div>
    </div>

    <div class="tos-container">
        <div class="tos-header">
            <h1><i class="fas fa-file-contract"></i> <?php echo $t['title']; ?></h1>
            <p><?php echo $t['last_updated']; ?>: <?php echo date('F j, Y'); ?></p>
        </div>

        <div class="tos-content">
            <div class="tos-section">
                <h2><?php echo $t['acceptance']; ?></h2>
                <p><?php echo $t['acceptance_text']; ?></p>
            </div>

            <div class="tos-section">
                <h2><?php echo $t['service_desc']; ?></h2>
                <p><?php echo $t['service_text']; ?></p>
                
                <div class="feature-highlight">
                    <h3><i class="fas fa-lock"></i> <?php echo $t['private_storage']; ?></h3>
                    <p><?php echo $t['private_text']; ?></p>
                    <ul>
                        <li><strong><?php echo explode(':', $t['private_access'])[0]; ?>:</strong> <?php echo explode(':', $t['private_access'], 2)[1]; ?></li>
                        <li><strong><?php echo explode(':', $t['unlimited_storage'])[0]; ?>:</strong> <?php echo explode(':', $t['unlimited_storage'], 2)[1]; ?></li>
                        <li><strong><?php echo explode(':', $t['cross_device'])[0]; ?>:</strong> <?php echo explode(':', $t['cross_device'], 2)[1]; ?></li>
                        <li><strong><?php echo explode(':', $t['auto_backup'])[0]; ?>:</strong> <?php echo explode(':', $t['auto_backup'], 2)[1]; ?></li>
                        <li><strong><?php echo explode(':', $t['privacy_protect'])[0]; ?>:</strong> <?php echo explode(':', $t['privacy_protect'], 2)[1]; ?></li>
                        <li><strong><?php echo explode(':', $t['full_quality'])[0]; ?>:</strong> <?php echo explode(':', $t['full_quality'], 2)[1]; ?></li>
                        <li><strong><?php echo explode(':', $t['controlled_share'])[0]; ?>:</strong> <?php echo explode(':', $t['controlled_share'], 2)[1]; ?></li>
                        <li><strong><?php echo explode(':', $t['ad_free'])[0]; ?>:</strong> <?php echo explode(':', $t['ad_free'], 2)[1]; ?></li>
                    </ul>
                </div>
            </div>

            <div class="tos-section">
                <h2><?php echo $t['user_responsibilities']; ?></h2>
                <ul>
                    <li><?php echo $t['user_resp1']; ?></li>
                    <li><?php echo $t['user_resp2']; ?></li>
                    <li><?php echo $t['user_resp3']; ?></li>
                    <li><?php echo $t['user_resp4']; ?></li>
                </ul>
            </div>

            <div class="tos-section">
                <h2><?php echo $t['prohibited_content']; ?></h2>
                <p><?php echo $t['prohibited_text']; ?></p>
                <ul>
                    <li><?php echo $t['prohibited1']; ?></li>
                    <li><?php echo $t['prohibited2']; ?></li>
                    <li><?php echo $t['prohibited3']; ?></li>
                    <li><?php echo $t['prohibited4']; ?></li>
                    <li><?php echo $t['prohibited5']; ?></li>
                </ul>
            </div>

            <div class="tos-section">
                <h2><?php echo $t['privacy_security']; ?></h2>
                <p><?php echo $t['privacy_text']; ?></p>
                <ul>
                    <li><?php echo $t['privacy1']; ?></li>
                    <li><?php echo $t['privacy2']; ?></li>
                    <li><?php echo $t['privacy3']; ?></li>
                </ul>
                <p><?php echo $t['privacy_note']; ?></p>
            </div>

            <div class="tos-section">
                <h2><?php echo $t['account_termination']; ?></h2>
                <p><?php echo $t['account_text']; ?></p>
                <ul>
                    <li><?php echo $t['account1']; ?></li>
                    <li><?php echo $t['account2']; ?></li>
                    <li><?php echo $t['account3']; ?></li>
                </ul>
            </div>

            <div class="tos-section">
                <h2><?php echo $t['warranty']; ?></h2>
                <p><?php echo $t['warranty_text']; ?></p>
                <ul>
                    <li><?php echo $t['warranty1']; ?></li>
                    <li><?php echo $t['warranty2']; ?></li>
                    <li><?php echo $t['warranty3']; ?></li>
                </ul>
            </div>

            <div class="tos-section">
                <h2><?php echo $t['liability']; ?></h2>
                <p><?php echo $t['liability_text']; ?></p>
                <ul>
                    <li><?php echo $t['liability1']; ?></li>
                    <li><?php echo $t['liability2']; ?></li>
                    <li><?php echo $t['liability3']; ?></li>
                    <li><?php echo $t['liability4']; ?></li>
                </ul>
            </div>

            <div class="tos-section">
                <h2><?php echo $t['changes']; ?></h2>
                <p><?php echo $t['changes_text']; ?></p>
            </div>

            <div class="footer">
            <p>© <?php echo date('Y'); ?> File Management System. All rights reserved.</p>
            <div class="footer-links">
           
                <a href="index.php">Home Page</a>
                <a href=" Privacy Policy.php">Privacy Policy</a>
                <a href="Contact Us.php">Contact Us</a>
                <a href="#">Support</a>
            </div>
            </div>
        </div>
            </div>
        </div>
    </div>

    <script>
        function toggleLanguageOptions() {
            document.getElementById('langOptions').classList.toggle('show-lang-options');
        }
        
        // Close language options when clicking outside
        window.onclick = function(event) {
            if (!event.target.matches('.lang-btn') && !event.target.closest('.lang-options')) {
                var dropdowns = document.getElementsByClassName("lang-options");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show-lang-options')) {
                        openDropdown.classList.remove('show-lang-options');
                    }
                }
            }
        }
    </script>
</body>
</html>