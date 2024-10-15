import { Button } from "@/components/ui/button";

export default function Home({ name }) {
    return (
        <>
            <div className="flex flex-col items-center justify-center min-h-screen">
                <h1 className="text-2xl font-bold">Halo {name}</h1>
            </div>
            <div>
                <Button>Click me</Button>
            </div>
        </>
    );
}
