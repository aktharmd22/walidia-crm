import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import type { ReactNode } from 'react'
import { Head, Link, router } from '@inertiajs/react'
import type { ColumnDef } from '@tanstack/react-table'
import { Archive, Plus } from 'lucide-react'
import { BulkBar, PageHeader, Pagination, Toolbar } from '@/components/shell/Page'
import { Button } from '@/ui/Button'
import { DataTable } from '@/ui/DataTable'
import { Drawer, Tabs } from '@/ui/Overlays'
import { Select } from '@/ui/Field'
import { EmptyState } from '@/ui/Primitives'
import type { Paginated } from '@/types'

export interface FilterField {
  key: string
  label: string
  options: { value: string | number; label: string }[]
}

export interface ResourceIndexProps<T> {
  title: string
  description?: string
  rows: Paginated<T>
  columns: ColumnDef<T, unknown>[]
  /** Current filter/search/sort state, echoed back by the server. */
  filters: Record<string, unknown>
  filterFields?: FilterField[]
  can?: { create?: boolean; export?: boolean; import?: boolean }
  baseUrl: string
  createUrl?: string
  createLabel?: string
  searchPlaceholder?: string
  rowKey: (row: T) => string | number
  rowUrl?: (row: T) => string
  mobileCard?: (row: T) => ReactNode
  bulkActions?: { key: string; label: string; destructive?: boolean; value?: string }[]
  metrics?: ReactNode
  scopeTabs?: { value: string; label: string; count?: number }[]
  scope?: string
  onScopeChange?: (scope: string) => void
  emptyTitle?: string
  emptyDescription?: string
  children?: ReactNode
}

/**
 * The list screen, once.
 *
 * Every index in the platform is this component with different columns, so
 * search, filtering, sorting, paging, selection, bulk actions and the empty
 * state behave identically everywhere — and a fix lands in all sixty screens
 * at the same time.
 */
export function ResourceIndex<T>({
  title,
  description,
  rows,
  columns,
  filters,
  filterFields = [],
  can = {},
  baseUrl,
  createUrl,
  createLabel,
  searchPlaceholder = 'Search…',
  rowKey,
  rowUrl,
  mobileCard,
  bulkActions = [],
  metrics,
  scopeTabs,
  scope,
  onScopeChange,
  emptyTitle,
  emptyDescription,
  children,
}: ResourceIndexProps<T>) {
  const [search, setSearch] = useState(String(filters.search ?? ''))
  const [selected, setSelected] = useState<(string | number)[]>([])
  const [filterOpen, setFilterOpen] = useState(false)
  const firstRender = useRef(true)

  const visit = useCallback(
    (params: Record<string, string | number | boolean>) => {
      const query: Record<string, string> = {}

      for (const [key, value] of Object.entries({ ...filters, ...params })) {
        if (value === null || value === undefined || value === '' || value === false) continue
        query[key] = String(value)
      }

      router.get(baseUrl, query, { preserveState: true, preserveScroll: true, replace: true })
    },
    [baseUrl, filters],
  )

  // Debounced search: typing should not fire a request per keystroke, and the
  // URL should still be shareable when they stop.
  useEffect(() => {
    if (firstRender.current) {
      firstRender.current = false
      return
    }

    const timer = window.setTimeout(() => {
      if (search !== (filters.search ?? '')) visit({ search, page: 1 })
    }, 300)

    return () => window.clearTimeout(timer)
  }, [search]) // eslint-disable-line react-hooks/exhaustive-deps

  const activeFilterCount = useMemo(
    () => filterFields.filter((field) => filters[field.key] && filters[field.key] !== 'all').length,
    [filterFields, filters],
  )

  function runBulk(action: string, value?: string) {
    router.post(
      `${baseUrl}/bulk`,
      { action, ids: selected, value },
      { preserveScroll: true, onSuccess: () => setSelected([]) },
    )
  }

  return (
    <>
      <Head title={title} />

      <PageHeader
        title={title}
        description={description}
        actions={
          <>
            <Link href={`${baseUrl}/archive`}>
              <Button variant="ghost" icon={<Archive className="size-4" />}>
                Archive
              </Button>
            </Link>
            {can.create && createUrl && (
              <Link href={createUrl}>
                <Button variant="primary" icon={<Plus className="size-4" />}>
                  {createLabel ?? 'New'}
                </Button>
              </Link>
            )}
          </>
        }
      />

      {metrics}

      <Toolbar
        search={search}
        onSearchChange={setSearch}
        searchPlaceholder={searchPlaceholder}
        onFilter={filterFields.length > 0 ? () => setFilterOpen(true) : undefined}
        filterCount={activeFilterCount}
        exportHref={can.export ? `${baseUrl}/export?${new URLSearchParams(
          Object.entries(filters).reduce<Record<string, string>>((accumulator, [key, value]) => {
            if (value !== null && value !== undefined && value !== '') accumulator[key] = String(value)
            return accumulator
          }, {}),
        ).toString()}` : undefined}
      />

      {scopeTabs && scope && onScopeChange && (
        <Tabs value={scope} onValueChange={onScopeChange} items={scopeTabs} />
      )}

      {children}

      {bulkActions.length > 0 && (
        <BulkBar count={selected.length} onClear={() => setSelected([])}>
          {bulkActions.map((action) => (
            <Button
              key={action.key}
              size="sm"
              variant={action.destructive ? 'destructive' : 'secondary'}
              onClick={() => runBulk(action.key, action.value)}
            >
              {action.label}
            </Button>
          ))}
        </BulkBar>
      )}

      <DataTable
        columns={columns}
        data={rows.data}
        rowKey={rowKey}
        selectable={bulkActions.length > 0}
        onSelectionChange={setSelected}
        sort={String(filters.sort ?? '')}
        onSortChange={(sort) => visit({ sort, page: 1 })}
        onRowClick={rowUrl ? (row) => router.visit(rowUrl(row)) : undefined}
        mobileCard={mobileCard}
        empty={
          <EmptyState
            title={emptyTitle ?? `No ${title.toLowerCase()} yet`}
            description={emptyDescription ?? 'Records you create or are assigned will appear here.'}
            action={
              can.create && createUrl ? (
                <Link href={createUrl}>
                  <Button variant="primary">{createLabel ?? 'New'}</Button>
                </Link>
              ) : undefined
            }
          />
        }
      />

      <Pagination page={rows} onNavigate={(url) => router.visit(url, { preserveState: true, preserveScroll: true })} />

      <Drawer
        open={filterOpen}
        onOpenChange={setFilterOpen}
        title="Filter"
        description="Filters apply to the list and to anything you export from it."
        footer={
          <>
            <Button
              variant="ghost"
              onClick={() => {
                const cleared = filterFields.reduce<Record<string, string>>((accumulator, field) => {
                  accumulator[field.key] = ''
                  return accumulator
                }, {})
                visit({ ...cleared, page: 1 })
                setFilterOpen(false)
              }}
            >
              Clear all
            </Button>
            <Button variant="primary" onClick={() => setFilterOpen(false)}>
              Done
            </Button>
          </>
        }
      >
        <div className="flex flex-col gap-3">
          {filterFields.map((field) => (
            <Select
              key={field.key}
              label={field.label}
              value={String(filters[field.key] ?? '')}
              placeholder="Any"
              options={field.options}
              onChange={(event) => visit({ [field.key]: event.target.value, page: 1 })}
            />
          ))}
        </div>
      </Drawer>
    </>
  )
}
