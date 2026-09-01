import { useMemo, useState } from 'react'
import type { ReactNode } from 'react'
import {
  flexRender,
  getCoreRowModel,
  useReactTable,
  type ColumnDef,
  type RowSelectionState,
} from '@tanstack/react-table'
import { ChevronDown, ChevronUp, ChevronsUpDown } from 'lucide-react'
import { cn } from '@/lib/cn'
import { Skeleton } from '@/ui/Primitives'

/**
 * Column priority drives responsive shedding: 1 never drops, 3 drops first.
 * Declared as data so column visibility is not media-query guesswork.
 */
export type ColumnPriority = 1 | 2 | 3

export interface TableColumnMeta {
  priority?: ColumnPriority
  align?: 'start' | 'end'
  numeric?: boolean
  /** Shown in the stacked mobile card. Defaults to true for priority 1–2. */
  mobile?: boolean
}

declare module '@tanstack/react-table' {
  /* eslint-disable @typescript-eslint/no-unused-vars, @typescript-eslint/no-empty-object-type */
  interface ColumnMeta<TData extends unknown, TValue> extends TableColumnMeta {}
  /* eslint-enable @typescript-eslint/no-unused-vars, @typescript-eslint/no-empty-object-type */
}

const priorityClass: Record<ColumnPriority, string> = {
  1: '',
  2: 'hidden md:table-cell',
  3: 'hidden xl:table-cell',
}

export interface DataTableProps<T> {
  columns: ColumnDef<T, unknown>[]
  data: T[]
  /** Current server sort, e.g. "-created_at". */
  sort?: string | null
  onSortChange?: (sort: string) => void
  rowKey: (row: T) => string | number
  onRowClick?: (row: T) => void
  selectable?: boolean
  onSelectionChange?: (keys: (string | number)[]) => void
  empty?: ReactNode
  loading?: boolean
  /** Rendered below md as a stacked card per row. */
  mobileCard?: (row: T) => ReactNode
  className?: string
}

export function DataTable<T>({
  columns,
  data,
  sort,
  onSortChange,
  rowKey,
  onRowClick,
  selectable = false,
  onSelectionChange,
  empty,
  loading = false,
  mobileCard,
  className,
}: DataTableProps<T>) {
  const [selection, setSelection] = useState<RowSelectionState>({})

  const table = useReactTable({
    data,
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualSorting: true,
    manualPagination: true,
    manualFiltering: true,
    enableRowSelection: selectable,
    state: { rowSelection: selection },
    getRowId: (row) => String(rowKey(row)),
    onRowSelectionChange: (updater) => {
      const next = typeof updater === 'function' ? updater(selection) : updater
      setSelection(next)
      onSelectionChange?.(Object.keys(next).filter((key) => next[key]))
    },
  })

  const currentSort = useMemo(() => {
    if (!sort) return { id: null as string | null, desc: false }
    return sort.startsWith('-') ? { id: sort.slice(1), desc: true } : { id: sort, desc: false }
  }, [sort])

  function toggleSort(columnId: string) {
    if (!onSortChange) return
    const desc = currentSort.id === columnId && !currentSort.desc
    onSortChange(`${desc ? '-' : ''}${columnId}`)
  }

  if (!loading && data.length === 0 && empty) {
    return <div className="bg-hull border border-line rounded-card">{empty}</div>
  }

  return (
    <div className={cn('bg-hull border border-line rounded-card overflow-hidden', className)}>
      {/* Stacked cards below md when a mobile renderer is supplied. */}
      {mobileCard && (
        <ul className="md:hidden divide-y divide-line">
          {loading
            ? Array.from({ length: 5 }).map((_, index) => (
                <li key={index} className="p-4">
                  <Skeleton className="h-4 w-1/2" />
                </li>
              ))
            : data.map((row) => (
                <li
                  key={rowKey(row)}
                  className={cn('p-4', onRowClick && 'cursor-pointer active:bg-deck')}
                  onClick={onRowClick ? () => onRowClick(row) : undefined}
                >
                  {mobileCard(row)}
                </li>
              ))}
        </ul>
      )}

      <div className={cn('overflow-x-auto', mobileCard && 'hidden md:block')}>
        <table className="w-full border-collapse">
          <thead>
            {table.getHeaderGroups().map((headerGroup) => (
              <tr key={headerGroup.id} className="bg-deck">
                {selectable && (
                  <th scope="col" className="w-10 px-4 py-3">
                    <input
                      type="checkbox"
                      aria-label="Select all rows"
                      className="form-checkbox size-4 rounded-[3px] border-line text-accent focus:ring-0"
                      checked={table.getIsAllRowsSelected()}
                      ref={(el) => {
                        if (el) el.indeterminate = table.getIsSomeRowsSelected()
                      }}
                      onChange={table.getToggleAllRowsSelectedHandler()}
                    />
                  </th>
                )}
                {headerGroup.headers.map((header) => {
                  const meta = (header.column.columnDef.meta ?? {}) as TableColumnMeta
                  const sortable = header.column.columnDef.enableSorting !== false && Boolean(onSortChange)
                  const isSorted = currentSort.id === header.column.id

                  return (
                    <th
                      key={header.id}
                      scope="col"
                      className={cn(
                        'px-4 py-3 text-micro text-ink-faint font-medium whitespace-nowrap border-b border-line',
                        meta.align === 'end' ? 'text-end' : 'text-start',
                        priorityClass[meta.priority ?? 1],
                      )}
                    >
                      {sortable ? (
                        <button
                          type="button"
                          onClick={() => toggleSort(header.column.id)}
                          className="inline-flex items-center gap-2 hover:text-ink"
                          aria-label={`Sort by ${header.column.id}`}
                        >
                          {flexRender(header.column.columnDef.header, header.getContext())}
                          {isSorted ? (
                            currentSort.desc ? (
                              <ChevronDown className="size-3" aria-hidden />
                            ) : (
                              <ChevronUp className="size-3" aria-hidden />
                            )
                          ) : (
                            <ChevronsUpDown className="size-3 opacity-50" aria-hidden />
                          )}
                        </button>
                      ) : (
                        flexRender(header.column.columnDef.header, header.getContext())
                      )}
                    </th>
                  )
                })}
              </tr>
            ))}
          </thead>

          <tbody>
            {loading
              ? Array.from({ length: 6 }).map((_, index) => (
                  <tr key={index} className="border-b border-line last:border-0">
                    {selectable && <td className="px-4" />}
                    {table.getAllLeafColumns().map((column) => {
                      const meta = (column.columnDef.meta ?? {}) as TableColumnMeta
                      return (
                        <td key={column.id} className={cn('h-row px-4', priorityClass[meta.priority ?? 1])}>
                          <Skeleton className="h-3 w-24" />
                        </td>
                      )
                    })}
                  </tr>
                ))
              : table.getRowModel().rows.map((row) => (
                  <tr
                    key={row.id}
                    onClick={onRowClick ? () => onRowClick(row.original) : undefined}
                    className={cn(
                      'border-b border-line last:border-0 transition-colors duration-fast',
                      onRowClick && 'cursor-pointer hover:bg-deck',
                      row.getIsSelected() && 'bg-accent-soft',
                    )}
                  >
                    {selectable && (
                      <td className="px-4">
                        <input
                          type="checkbox"
                          aria-label="Select row"
                          className="form-checkbox size-4 rounded-[3px] border-line text-accent focus:ring-0"
                          checked={row.getIsSelected()}
                          onChange={row.getToggleSelectedHandler()}
                          onClick={(event) => event.stopPropagation()}
                        />
                      </td>
                    )}
                    {row.getVisibleCells().map((cell) => {
                      const meta = (cell.column.columnDef.meta ?? {}) as TableColumnMeta
                      return (
                        <td
                          key={cell.id}
                          className={cn(
                            'h-row px-4 py-2 text-body text-ink-soft align-middle',
                            meta.align === 'end' ? 'text-end' : 'text-start',
                            meta.numeric && 'numeric text-ink',
                            priorityClass[meta.priority ?? 1],
                          )}
                        >
                          {flexRender(cell.column.columnDef.cell, cell.getContext())}
                        </td>
                      )
                    })}
                  </tr>
                ))}
          </tbody>
        </table>
      </div>
    </div>
  )
}
