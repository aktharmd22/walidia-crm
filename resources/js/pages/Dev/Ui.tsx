import { useState } from 'react'
import { Head } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { AlertTriangle, MoreHorizontal, Phone, Ship, Trash2, Wallet } from 'lucide-react'
import { PageHeader, MetricCard, Toolbar, BulkBar, Pagination } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { Checkbox, Input, Select, Textarea } from '@/ui/Field'
import { Card, CardBody, CardHeader, CardTitle, DateText, EmptyState, IdentityCell, Money, Num, OverflowChips, Skeleton } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'
import { DataTable } from '@/ui/DataTable'
import { Drawer, DropdownMenu, Modal, Tabs, Tooltip } from '@/ui/Overlays'
import type { StatusTone, Paginated } from '@/types'

/**
 * The component gallery. Local only. Every primitive in every state, so a
 * regression shows up here before it shows up in a module.
 */

interface DemoRow {
  id: number
  yacht: string
  client: string
  companies: string[]
  status: string
  tone: StatusTone
  value: string
  date: string
}

const rows: DemoRow[] = [
  { id: 1, yacht: 'Serenity IX', client: 'Al Mansouri Group', companies: ['Al Mansouri Group', 'Gulf Horizon'], status: 'Confirmed', tone: 'info', value: '185000.00', date: '2026-03-20' },
  { id: 2, yacht: 'Northern Star', client: 'Gulf Horizon Concierge', companies: ['Gulf Horizon Concierge'], status: 'Deposit due', tone: 'warning', value: '420000.00', date: '2026-03-24' },
  { id: 3, yacht: 'Azure Dawn', client: 'Doha Marine Ventures', companies: ['Doha Marine Ventures', 'Pearl DMC', 'Lusail Charter'], status: 'Completed', tone: 'success', value: '96500.00', date: '2026-03-02' },
  { id: 4, yacht: 'Blue Meridian', client: 'Seychelles Blue DMC', companies: [], status: 'On hold', tone: 'neutral', value: '310000.00', date: '2026-03-28' },
  { id: 5, yacht: 'Desert Pearl', client: 'Private client', companies: ['Private office'], status: 'Cancelled', tone: 'danger', value: '75000.00', date: '2026-02-18' },
]

const columns: ColumnDef<DemoRow, unknown>[] = [
  {
    id: 'yacht',
    header: 'Yacht / client',
    cell: ({ row }) => <IdentityCell name={row.original.yacht} subtitle={row.original.client} />,
    meta: { priority: 1 },
  },
  {
    id: 'status',
    header: 'Status',
    cell: ({ row }) => <StatusPill tone={row.original.tone}>{row.original.status}</StatusPill>,
    meta: { priority: 1 },
  },
  {
    id: 'companies',
    header: 'Companies',
    cell: ({ row }) => <OverflowChips items={row.original.companies} />,
    meta: { priority: 3 },
  },
  {
    id: 'value',
    header: 'Charter value',
    cell: ({ row }) => <Money amount={row.original.value} />,
    meta: { priority: 2, align: 'end', numeric: true },
  },
  {
    id: 'date',
    header: 'Departure',
    cell: ({ row }) => <DateText value={row.original.date} />,
    meta: { priority: 2, align: 'end' },
  },
  {
    id: 'actions',
    header: '',
    enableSorting: false,
    cell: () => (
      <span className="flex items-center justify-end gap-1">
        <Tooltip content="Call client">
          <button type="button" className="rounded-pill p-2 text-ink-faint hover:bg-deck hover:text-ink" aria-label="Call client">
            <Phone className="size-4" aria-hidden />
          </button>
        </Tooltip>
        <DropdownMenu
          label="MENU"
          items={[
            { key: 'details', label: 'Details' },
            { key: 'edit', label: 'Edit' },
            { key: 'archive', label: 'Archive', icon: <Trash2 className="size-4" />, destructive: true, separatorBefore: true },
          ]}
          trigger={
            <button type="button" className="rounded-pill p-2 text-ink-faint hover:bg-deck hover:text-ink" aria-label="More actions">
              <MoreHorizontal className="size-4" aria-hidden />
            </button>
          }
        />
      </span>
    ),
    meta: { priority: 1, align: 'end' },
  },
]

const page: Paginated<DemoRow> = {
  data: rows,
  links: [
    { url: null, label: 'Previous', active: false },
    { url: '#', label: '1', active: true },
    { url: '#', label: '2', active: false },
    { url: '#', label: '3', active: false },
    { url: '#', label: 'Next', active: false },
  ],
  meta: { current_page: 1, from: 1, last_page: 3, per_page: 5, to: 5, total: 14 },
}

function Section({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <Card>
      <CardHeader>
        <CardTitle>{title}</CardTitle>
      </CardHeader>
      <CardBody className="flex flex-wrap items-start gap-4">{children}</CardBody>
    </Card>
  )
}

export default function Ui() {
  const [modalOpen, setModalOpen] = useState(false)
  const [drawerOpen, setDrawerOpen] = useState(false)
  const [tab, setTab] = useState('all')
  const [search, setSearch] = useState('')
  const [selected, setSelected] = useState<(string | number)[]>([])

  return (
    <>
      <Head title="Component gallery" />

      <PageHeader
        title="Component gallery"
        description="Every primitive, in every state. Switch chrome from the sidebar account menu to check both themes."
        actions={<Button variant="primary">+ Primary action</Button>}
      />

      <Toolbar
        search={search}
        onSearchChange={setSearch}
        searchPlaceholder="Search components…"
        onFilter={() => undefined}
        filterCount={2}
        exportHref="#"
      >
        <Button variant="primary">+ New booking</Button>
      </Toolbar>

      <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <MetricCard label="Confirmed" value={<Num value={14} />} icon={<Ship className="size-4" />} tone="info" delta="▲ +3" comparison="vs last month" />
        <MetricCard label="Awaiting deposit" value={<Num value={5} />} icon={<AlertTriangle className="size-4" />} tone="warning" delta="2 overdue" deltaTone="danger" />
        <MetricCard label="Booked value" value={<Money amount="4920000" compact withCurrency={false} />} icon={<Wallet className="size-4" />} tone="success" comparison="AED · quarter to date" />
        <MetricCard
          label="Utilisation"
          value="62%"
          icon={<Ship className="size-4" />}
          tone="neutral"
          menu={[{ key: 'a', label: 'Open report' }]}
        />
      </div>

      <Tabs
        value={tab}
        onValueChange={setTab}
        items={[
          { value: 'all', label: 'All bookings', count: 14 },
          { value: 'mine', label: 'Mine', count: 4 },
        ]}
      />

      <BulkBar count={selected.length} onClear={() => setSelected([])}>
        <Button size="sm" variant="secondary">Assign</Button>
        <Button size="sm" variant="secondary">Export</Button>
        <Button size="sm" variant="destructive">Archive</Button>
      </BulkBar>

      <DataTable
        columns={columns}
        data={rows}
        rowKey={(row) => row.id}
        selectable
        onSelectionChange={setSelected}
        sort="-date"
        onSortChange={() => undefined}
        mobileCard={(row) => (
          <div className="flex flex-col gap-2">
            <IdentityCell name={row.yacht} subtitle={row.client} />
            <div className="flex items-center justify-between">
              <StatusPill tone={row.tone}>{row.status}</StatusPill>
              <Money amount={row.value} />
            </div>
          </div>
        )}
      />

      <Pagination page={page} onNavigate={() => undefined} />

      <Section title="Buttons">
        <Button variant="primary">Primary</Button>
        <Button variant="secondary">Secondary</Button>
        <Button variant="ghost">Ghost</Button>
        <Button variant="destructive">Destructive</Button>
        <Button variant="link">Link</Button>
        <Button variant="primary" loading>Saving</Button>
        <Button variant="primary" disabled>Disabled</Button>
        <Button variant="secondary" size="sm">Small</Button>
        <Button variant="secondary" size="lg">Large</Button>
      </Section>

      <Section title="Status pills">
        <StatusPill tone="success">Completed</StatusPill>
        <StatusPill tone="info">Confirmed</StatusPill>
        <StatusPill tone="warning">Awaiting deposit</StatusPill>
        <StatusPill tone="attention">Offer received</StatusPill>
        <StatusPill tone="danger">Cancelled</StatusPill>
        <StatusPill tone="neutral">Draft</StatusPill>
      </Section>

      <Section title="Form controls">
        <div className="grid w-full gap-3 md:grid-cols-3">
          <Input label="Client name" placeholder="Full name" defaultValue="Al Mansouri Group" />
          <Input label="Charter value" numeric defaultValue="185000.00" help="AED, excluding VAT" />
          <Input label="Email" type="email" error="That email is already on another client." defaultValue="not-an-email" />
          <Select
            label="Experience type"
            placeholder="Select…"
            options={[
              { value: 'day', label: 'Day charter' },
              { value: 'overnight', label: 'Overnight' },
              { value: 'corporate', label: 'Corporate event' },
            ]}
          />
          <Textarea label="Itinerary notes" placeholder="Departure from Yas Marina…" />
          <div className="flex flex-col justify-end gap-3">
            <Checkbox label="Requires proof of funds" description="Blocks offers until a document is on file." defaultChecked />
            <Checkbox label="VIP handling" />
          </div>
        </div>
      </Section>

      <Section title="Figures">
        <div className="grid w-full gap-3 md:grid-cols-4">
          <div><p className="text-micro text-ink-faint">Money</p><Money amount="185000" className="text-h2" /></div>
          <div><p className="text-micro text-ink-faint">Compact</p><Money amount="4920000" compact className="text-h2" /></div>
          <div><p className="text-micro text-ink-faint">Date</p><DateText value="2026-03-20" className="text-h2" /></div>
          <div><p className="text-micro text-ink-faint">Count</p><Num value={1284} className="text-h2" /></div>
        </div>
      </Section>

      <Section title="Overlays">
        <Button variant="secondary" onClick={() => setModalOpen(true)}>Open modal</Button>
        <Button variant="secondary" onClick={() => setDrawerOpen(true)}>Open drawer</Button>
        <Tooltip content="Tooltips explain, they do not decorate.">
          <Button variant="ghost">Hover me</Button>
        </Tooltip>
      </Section>

      <Section title="Loading and empty states">
        <div className="grid w-full gap-4 md:grid-cols-2">
          <div className="flex flex-col gap-2">
            <Skeleton className="h-3 w-40" />
            <Skeleton className="h-3 w-64" />
            <Skeleton className="h-3 w-24" />
          </div>
          <EmptyState
            icon={<Ship className="size-5" aria-hidden />}
            title="No bookings yet"
            description="Confirmed charters appear here with their operational release state."
            action={<Button variant="primary">+ New booking</Button>}
          />
        </div>
      </Section>

      <Modal
        open={modalOpen}
        onOpenChange={setModalOpen}
        title="Archive this booking?"
        description="It moves to Archive and can be restored. Nothing is deleted."
        footer={
          <>
            <Button variant="secondary" onClick={() => setModalOpen(false)}>Cancel</Button>
            <Button variant="destructive" onClick={() => setModalOpen(false)}>Archive booking</Button>
          </>
        }
      >
        <p className="text-body text-ink-soft">
          BK-2026-0041 · Serenity IX · Al Mansouri Group. The cost sheet and invoices stay linked to it.
        </p>
      </Modal>

      <Drawer
        open={drawerOpen}
        onOpenChange={setDrawerOpen}
        title="New charter enquiry"
        description="Drawer on desktop, full page below 768px."
        footer={
          <>
            <Button variant="secondary" onClick={() => setDrawerOpen(false)}>Cancel</Button>
            <Button variant="primary" onClick={() => setDrawerOpen(false)}>Create enquiry</Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          <Input label="Client" placeholder="Search clients…" />
          <Input label="Requested date" type="date" />
          <Input label="Guests" numeric defaultValue="12" />
          <Textarea label="Notes" />
        </div>
      </Drawer>
    </>
  )
}
