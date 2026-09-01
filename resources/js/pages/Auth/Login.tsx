import { useForm, Head } from '@inertiajs/react'
import type { FormEvent } from 'react'
import { Button } from '@/ui/Button'
import { Checkbox, Input } from '@/ui/Field'

export default function Login({ status, canResetPassword }: { status?: string; canResetPassword?: boolean }) {
  const { data, setData, post, processing, errors } = useForm({
    email: '',
    password: '',
    remember: false as boolean,
  })

  function submit(event: FormEvent) {
    event.preventDefault()
    post('/login')
  }

  return (
    <div className="grid min-h-screen lg:grid-cols-2">
      <Head title="Sign in" />

      {/* Brand panel — hidden on small screens, where the form is the whole job. */}
      <div className="hidden flex-col justify-between bg-[color:var(--ink)] p-12 lg:flex">
        <div className="flex items-center gap-3">
          <span className="grid size-9 place-items-center rounded-card bg-accent text-accent-on text-h2">W</span>
          <span className="text-h2 text-white">Walidia Yachts</span>
        </div>
        <div className="max-w-prose">
          <p className="text-display text-white">Charter, brokerage and management, on one record.</p>
          <p className="mt-4 text-body text-[color:var(--ink-faint)]">
            Abu Dhabi · Dubai · Doha · Riyadh · Muscat · Victoria · Malé
          </p>
        </div>
        <p className="text-small text-[color:var(--ink-faint)]">
          Access is logged. Two-factor authentication is required for every account.
        </p>
      </div>

      <div className="flex items-center justify-center bg-hull px-4 py-12">
        <div className="w-full max-w-[380px]">
          <div className="mb-8 flex items-center gap-3 lg:hidden">
            <span className="grid size-9 place-items-center rounded-card bg-accent text-accent-on text-h2">W</span>
            <span className="text-h2 text-ink">Walidia Yachts</span>
          </div>

          <h1 className="text-h1 text-ink">Sign in</h1>
          <p className="mt-2 text-body text-ink-soft">Use your Walidia account.</p>

          {status && (
            <p className="mt-4 rounded-card border border-success bg-success-bg px-3 py-2 text-small text-success">
              {status}
            </p>
          )}

          <form onSubmit={submit} className="mt-6 flex flex-col gap-3">
            <Input
              label="Email"
              type="email"
              name="email"
              autoComplete="username"
              required
              autoFocus
              value={data.email}
              error={errors.email}
              onChange={(event) => setData('email', event.target.value)}
            />

            <Input
              label="Password"
              type="password"
              name="password"
              autoComplete="current-password"
              required
              value={data.password}
              error={errors.password}
              onChange={(event) => setData('password', event.target.value)}
            />

            <div className="flex items-center justify-between gap-3 pt-1">
              <Checkbox
                label="Keep me signed in"
                name="remember"
                checked={data.remember}
                onChange={(event) => setData('remember', event.target.checked)}
              />
              {canResetPassword && (
                <a href="/forgot-password" className="text-small text-accent hover:underline">
                  Forgot password?
                </a>
              )}
            </div>

            <Button type="submit" variant="primary" size="lg" block loading={processing} className="mt-3">
              Sign in
            </Button>
          </form>

          <p className="mt-6 text-small text-ink-faint">
            Sessions end after 8 hours of inactivity. You can review and revoke active sessions from your profile.
          </p>
        </div>
      </div>
    </div>
  )
}

Login.layout = undefined
