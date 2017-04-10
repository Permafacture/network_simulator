    from asyncio import get_event_loop, sleep, async
    from random import random

    class Node():

        def __init__(self, name, readers=[], writers=[]):
            self.name = name
            self.readers = []
            self.listener = self.listen()
            self.listener.send(None)

        def add_reader(self, reader):
            self.readers.append(reader.listener)

        def listen(self):
            while True:
                name, val = (yield)
                print("{} heard {:.3f} from {}".format(self.name, val, name))

        async def decide_to_write(self):
            while True:
                w = random()*2
                await sleep(w)
                await self.write(w)

        async def write(self, w):
            for reader in self.readers:
                reader.send((self.name, w))

    # connect all the nodes in a circle
    nodes = [Node(x) for x in ('bob', 'darnette', 'eloquise', 'daryll')]
    for n1, n2 in zip(nodes, nodes[1:]):
        n1.add_reader(n2)
    nodes[-1].add_reader(nodes[0])

    loop = get_event_loop()
    [async(n.decide_to_write()) for n in nodes]
    loop.run_forever()

# w = writer()
# wrap = writer_wrapper(w)
# wrap.send(None)  # prine the coroutine
# for i in range(4):
#     wrap.send(i)
