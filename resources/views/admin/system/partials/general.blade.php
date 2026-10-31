<x-ui.card title="General" subtitle="How Sharet introduces itself in emails, the dashboard and on the site.">
  <div class="grid gap-5 md:grid-cols-2">
    <x-ui.field label="Site name" name="name" model="d.values.name" required />
    <x-ui.field label="Slogan" name="slogan" model="d.values.slogan" />
    <x-ui.field label="Contact email" name="contact_email" type="email" model="d.values.contact_email" />
    <x-ui.field label="Contact phone" name="contact_phone" type="tel" model="d.values.contact_phone" />
    <x-ui.field label="Office address" name="address" model="d.values.address" class="md:col-span-2" />
  </div>
</x-ui.card>
