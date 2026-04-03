# Εφαρμογή Παρακολούθησης Πινάκων Διοριστέων
**Appointee Lists Tracking App**

Εφαρμογή παρακολούθησης των πινάκων διοριστέων της Εκπαιδευτικής Υπηρεσίας Κύπρου.
Επιτρέπει στους υποψηφίους να ελέγχουν την κατάταξή τους και στους διαχειριστές να
διαχειρίζονται τους πίνακες και τους χρήστες.

---

## Ομάδα Εργασίας

| Όνομα Φοιτητή   | Αριθμός Μητρώου | Ενότητα που Ανατέθηκε         |
|-----------------|-----------------|-------------------------------|
| Φοιτητής Α      | ΑΜ: 12345       | public/ – Δημόσια Σελίδα      |
| Φοιτητής Β      | ΑΜ: 12346       | admin/ – Dashboard Διαχειριστή |
| Φοιτητής Γ      | ΑΜ: 12347       | candidate/ – Dashboard Υποψηφίου |

---

## Δομή Αρχείων

```
project-root/
├── public/
│   ├── index.html        ← Δημόσια αρχική σελίδα (landing page)
│   └── style.css
├── admin/
│   ├── dashboard.html    ← Dashboard διαχειριστή
│   └── style.css
├── candidate/
│   ├── dashboard.html    ← Dashboard υποψηφίου
│   └── style.css
├── database/
│   ├── schema.sql        ← Δημιουργία πινάκων βάσης δεδομένων
│   └── seed.sql          ← Demo δεδομένα
├── includes/
│   └── db.php            ← PDO σύνδεση με MySQL
├── auth/
│   ├── register.php      ← Εγγραφή νέου χρήστη
│   ├── login.php         ← Σύνδεση χρήστη
│   └── logout.php        ← Αποσύνδεση
├── modules/
│   ├── dashboard.php     ← Κεντρική σελίδα (post-login)
│   └── list.php          ← Λίστα και αναζήτηση διοριστέων
└── README.md
```

---

## Οδηγίες Εκτέλεσης (Frontend)

Δεν απαιτείται server για το frontend. Ανοίξτε απευθείας στον browser:

```
Ανοίξτε το αρχείο public/index.html σε οποιονδήποτε browser για να δείτε τη δημόσια σελίδα.
```

Ή κάντε διπλό κλικ στο αρχείο `public/index.html` από την Εξερεύνηση Αρχείων.

### Πλοήγηση μεταξύ σελίδων:
- **Αρχική:** `public/index.html`
- **Admin Dashboard:** `admin/dashboard.html`
- **Candidate Dashboard:** `candidate/dashboard.html`

---

## Οδηγίες Εκτέλεσης (Backend – PHP)

Απαιτείται XAMPP ή παρόμοιο περιβάλλον.

1. Αντιγράψτε τον φάκελο στο `C:/xampp/htdocs/Mixaniki_Istou/`
2. Εκκινήστε Apache και MySQL από το XAMPP Control Panel
3. Εισάγετε `database/schema.sql` και `database/seed.sql` στο phpMyAdmin
4. Ανοίξτε: `http://localhost/Mixaniki_Istou/auth/login.php`

### Demo Λογαριασμοί:
| Email           | Κωδικός     | Ρόλος      |
|-----------------|-------------|------------|
| admin@demo.cy   | Admin1234!  | admin      |
| maria@demo.cy   | Maria1234!  | candidate  |
| nikos@demo.cy   | Nikos1234!  | candidate  |

---

## Τεχνολογίες

| Στρώμα   | Τεχνολογία                        |
|----------|-----------------------------------|
| Frontend | HTML5, CSS3 (Flexbox / Grid)      |
| Backend  | PHP 8.2, PDO                      |
| Database | MySQL / MariaDB                   |
| Fonts    | Google Fonts – Inter              |
| Server   | Apache (XAMPP)                    |
