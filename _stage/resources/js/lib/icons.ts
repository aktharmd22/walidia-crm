import {
  Anchor,
  BarChart3,
  Bell,
  Briefcase,
  Building2,
  CalendarDays,
  CheckSquare,
  ClipboardList,
  Compass,
  CreditCard,
  FileText,
  Gauge,
  Handshake,
  LayoutDashboard,
  LifeBuoy,
  MessageSquare,
  Receipt,
  Settings,
  Ship,
  ShieldCheck,
  Sparkles,
  Truck,
  UserRound,
  Users,
  Wrench,
  type LucideIcon,
} from 'lucide-react'

/**
 * Nav icons are addressed by name from the server-side nav tree, so the
 * navigation stays one definition in PHP rather than two that drift.
 */
export const navIcons: Record<string, LucideIcon> = {
  dashboard: LayoutDashboard,
  leads: Sparkles,
  clients: Users,
  charter: Ship,
  brokerage: Handshake,
  management: Wrench,
  fleet: Anchor,
  crew: UserRound,
  vendors: Truck,
  finance: Receipt,
  documents: FileText,
  compliance: ShieldCheck,
  communications: MessageSquare,
  automation: Gauge,
  tasks: CheckSquare,
  reports: BarChart3,
  settings: Settings,
  company: Building2,
  calendar: CalendarDays,
  checklist: ClipboardList,
  payments: CreditCard,
  alerts: Bell,
  safety: LifeBuoy,
  matching: Compass,
  portfolio: Briefcase,
}

export function navIcon(name: string): LucideIcon {
  return navIcons[name] ?? LayoutDashboard
}
