import { Head, Link } from '@inertiajs/react'
import { Tags } from 'lucide-react'
import { PageHeader } from '@/components/shell/Page'
import { Card, EmptyState, Num } from '@/ui/Primitives'
import { StatusPill } from '@/ui/StatusPill'

interface Category {
  id: number
  name: string
  requires_insurance: boolean
  requires_licence: boolean
  vendors_count: number
}

/** Categories carry the compliance rules — which is why they are not just labels. */
export default function VendorCategories({ categories = [] }: { categories?: Category[] }) {
  return (
    <>
      <Head title="Vendor categories" />

      <PageHeader
        title="Vendor categories"
        description="What a category demands — insurance, a trade licence — applies to every vendor inside it."
      />

      <Card>
        {categories.length === 0 ? (
          <EmptyState icon={<Tags className="size-5" aria-hidden />} title="No categories" description="Add one from Settings." />
        ) : (
          <ul className="divide-y divide-line">
            {categories.map((category) => (
              <li key={category.id} className="flex flex-wrap items-center gap-3 px-5 py-3">
                <Link
                  href={`/vendors?vendor_category_id=${category.id}`}
                  className="min-w-0 flex-1 text-h3 text-ink hover:text-accent-ink"
                >
                  {category.name}
                </Link>
                {category.requires_insurance && <StatusPill tone="info">Insurance required</StatusPill>}
                {category.requires_licence && <StatusPill tone="info">Licence required</StatusPill>}
                <span className="text-small text-ink-faint">
                  <Num value={category.vendors_count} /> vendors
                </span>
              </li>
            ))}
          </ul>
        )}
      </Card>
    </>
  )
}
