import { useState } from 'react';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { useShellProps } from '@/context/ShellPropsContext';

function submitLogoutForm() {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '/logout';

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

    if (csrf) {
        const tokenInput = document.createElement('input');
        tokenInput.type = 'hidden';
        tokenInput.name = '_token';
        tokenInput.value = csrf;
        form.appendChild(tokenInput);
    }

    document.body.appendChild(form);
    form.submit();
}

export function AppUserMenu() {
    const { auth } = useShellProps();
    const user = auth?.user;
    const [logoutOpen, setLogoutOpen] = useState(false);

    if (!user) {
        return null;
    }

    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button variant="ghost" size="icon" className="rounded-full">
                        <Avatar className="size-8">
                            {user.avatarUrl ? (
                                <AvatarImage src={user.avatarUrl} alt={user.name} />
                            ) : null}
                            <AvatarFallback className="bg-primary text-primary-foreground text-xs">
                                {user.initials}
                            </AvatarFallback>
                        </Avatar>
                        <span className="sr-only">Account menu</span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-56">
                    <DropdownMenuLabel className="font-normal">
                        <div className="flex flex-col space-y-1">
                            <p className="text-sm font-medium">{user.name}</p>
                            <p className="text-xs text-muted-foreground">{user.email}</p>
                        </div>
                    </DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem asChild>
                        <a href="/account">My account</a>
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                        className="text-destructive focus:text-destructive"
                        onSelect={(event) => {
                            event.preventDefault();
                            setLogoutOpen(true);
                        }}
                    >
                        Logout
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>

            <AlertDialog open={logoutOpen} onOpenChange={setLogoutOpen}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Log out?</AlertDialogTitle>
                        <AlertDialogDescription>
                            You will be signed out of the library admin portal. You can sign back in at any time.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancel</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={() => {
                                submitLogoutForm();
                                setLogoutOpen(false);
                            }}
                        >
                            Log out
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </>
    );
}
