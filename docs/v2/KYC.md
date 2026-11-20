# Verification (KYC) and ID reading

Levels live in `App\Support\Kyc` (details encrypted in `users.kyc`, photos on the private disk under `kyc/{user}`).

| Level | Needs | Unlocks |
|---|---|---|
| none | — | looking around; no money moves |
| **basic ("Verified")** | name, M-PESA number, ID front and back (a passport has no back), selfies centre / left / right | send, receive, withdraw at basic limits (`config/pricing.php`) |
| standard ("Bank grade") | basic + ID details, KRA PIN, address, work and income, next of kin, PEP answer, signature | higher limits, loans, cards, trade, registering a business |

The level is worked out from what is on file (`Kyc::refreshLevel`), never set by hand. Uploading the last photo
returns `verified: true` and the dashboard banner disappears.

## Reading the ID instead of typing it

When an ID photo is uploaded, `Kyc::storeDocument` asks the `IdReader` (integrations side) what the photo says and
fills any empty details: full name, ID number, date of birth, sex. What was read is kept under `id_read` so staff can
compare it with what the customer later edits.

- `App\Integrations\Contracts\IdReader`: the contract, so a KYC provider (Smile ID, an IPRS lookup) can replace it.
- `IdReader\TesseractIdReader`: OCR with the `tesseract` binary, then `IdReader\KenyanIdText` pulls out the fields:
  the labelled front ("ID NUMBER", "FULL NAMES", "DATE OF BIRTH", "SEX"; Maisha cards' "SURNAME" / "GIVEN NAMES") and
  the machine-readable lines on the back (`IDKYA…`). Only confident matches are returned.
- `IdReader\NullIdReader`: used when `ID_READER=none` or tesseract is missing; nothing is read, the customer types.
- Settings: `ID_READER` (`tesseract` | `none`), `TESSERACT_PATH`. Tests force `none` (phpunit.xml).

Tesseract is installed on the development machine but **not on the server**, so live runs with `ID_READER=none` for now.

Tests: `tests/Unit/KenyanIdTextTest.php` (front, back, unsure text) and the verification test in
`tests/Feature/VerificationAndIntegrationsTest.php` (photos verify, details filled from a fake reader).
