import { useForm, Head } from '@inertiajs/react'
import type { FormEvent } from 'react'
import { Button } from '@/ui/Button'
import { Checkbox, Input, PasswordInput } from '@/ui/Field'

/**
 * Sign in.
 *
 * Two columns that each do one job: a photograph that says what the company
 * does, and a form column with nothing in it but the form. The picture gets the
 * larger share on wide screens because it survives being big; the form does not,
 * so it stays on a 340px measure however wide the window gets.
 */
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
    <div className="min-h-screen lg:grid lg:grid-cols-[7fr_5fr] xl:grid-cols-[3fr_2fr]">
      <Head title="Sign in" />

      {/* Dubai by yacht. No type over it — the photograph is the whole message. */}
      <div className="relative hidden overflow-hidden bg-[color:var(--ink)] lg:block">
        <img
          src="/images/dubai-yacht.jpg"
          alt=""
          aria-hidden
          className="absolute inset-0 size-full object-cover"
        />
        {/* A whisper of ink at the seam, so the photograph meets the white
            column as an edge rather than a collision. */}
        <div
          className="absolute inset-y-0 end-0 w-32 bg-gradient-to-l from-black/25 to-transparent"
          aria-hidden
        />
      </div>

      <div className="flex min-h-screen items-center justify-center bg-hull px-6 py-10 sm:px-10 lg:min-h-0 lg:px-12">
        <div className="w-full max-w-[340px]">
          {/* Sized in absolutes: the spacing scale here is a fixed set of
              tokens, so h-14 and friends silently do nothing. */}
          <img
            src="/images/walidia-logo.png"
            alt="Walidia Yachts"
            className="h-[52px] w-auto object-contain object-left"
          />

          <div className="mt-10">
            <h1 className="text-h1 text-ink">Sign in</h1>
            <p className="mt-1 text-body text-ink-soft">Use your Walidia account.</p>
          </div>

          {status && (
            <p className="mt-6 rounded-card border border-success bg-success-bg px-3 py-2 text-small text-success">
              {status}
            </p>
          )}

          <form onSubmit={submit} className="mt-8 flex flex-col gap-4">
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

            <PasswordInput
              label="Password"
              name="password"
              autoComplete="current-password"
              required
              value={data.password}
              error={errors.password}
              onChange={(event) => setData('password', event.target.value)}
            />

            <div className="flex items-center justify-between gap-4">
              <Checkbox
                label="Keep me signed in"
                name="remember"
                checked={data.remember}
                onChange={(event) => setData('remember', event.target.checked)}
              />
              {canResetPassword && (
                <a
                  href="/forgot-password"
                  className="shrink-0 text-small text-accent hover:underline"
                >
                  Forgot password?
                </a>
              )}
            </div>

            <Button type="submit" variant="primary" size="lg" block loading={processing} className="mt-2">
              Sign in
            </Button>
          </form>

          <p className="mt-10 border-t border-line pt-5 text-small text-ink-faint">
            Sessions end after 8 hours of inactivity. You can review and revoke active sessions from your profile.
          </p>
        </div>
      </div>
    </div>
  )
}

Login.layout = undefined
