{{-- Website › Companies: the businesses Sharet runs. Data: GroupCompanyController@index as JSON. --}}
@extends('layouts.app')
@section('title', 'Companies')

@section('content')
<div x-data="dataTable({ url: '{{ route('admin.group-companies.index') }}', sort: 'sort' })">
  <x-ui.page-header title="Companies" subtitle="The businesses Sharet runs. Each one gets a card on sharet.africa/company and its own page. Order sets the layout: four in the first row, then three.">
    <x-ui.button variant="secondary" icon="arrow-top-right-on-square" href="{{ route('company') }}#companies" target="_blank">View on website</x-ui.button>
    <x-ui.button icon="plus" @click="sharet.panel('group-company', { form: { published: true } })">Add company</x-ui.button>
  </x-ui.page-header>

  <x-ui.stats :cols="2" class="mb-6">
    <x-ui.stat label="Companies" value="stats.total" hint="stats.total_hint" icon="building-office-2" tone="teal" />
    <x-ui.stat label="On the website" value="stats.published" hint="stats.published_hint" icon="globe-alt" tone="emerald" />
  </x-ui.stats>

  <x-ui.table title="Companies" placeholder="Search name or sector…" :columns="[
      ['key' => 'company', 'label' => 'Company', 'type' => 'person', 'sort' => 'name'],
      ['key' => 'sector', 'label' => 'Sector', 'type' => 'muted'],
      ['key' => 'sort', 'label' => 'Order', 'type' => 'number', 'sort' => 'sort'],
      ['key' => 'status', 'label' => 'Website', 'type' => 'pill'],
  ]">
    <x-slot:actions>
      <x-ui.row-menu>
        <x-ui.row-menu-item icon="pencil-square" @click="sharet.panel('group-company', row)">Edit</x-ui.row-menu-item>
        <x-ui.row-menu-item icon="arrow-top-right-on-square" x-bind:href="row.company.url" target="_blank">View page</x-ui.row-menu-item>
        <x-ui.row-menu-item icon="trash" danger
            @click="sharet.confirm({ title: 'Remove this company?', message: 'Its card and page come off the website.', tone: 'error', confirmText: 'Remove', details: [['Name', row.company.name], ['Sector', row.sector]] })
              .then(ok => ok && sharet.send('DELETE', '{{ url('admin/companies') }}/' + row.id).then(() => load()).catch(() => {}))">Remove</x-ui.row-menu-item>
      </x-ui.row-menu>
    </x-slot:actions>
  </x-ui.table>

  @include('admin.group-companies.partials.form')
</div>
@endsection
