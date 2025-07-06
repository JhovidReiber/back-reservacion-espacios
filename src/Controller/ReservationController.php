<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Space;
use App\Entity\User;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ReservationController extends AbstractController
{
    /**
     * @OA\Post(
     *     path="/api/reservations",
     *     summary="Crear una nueva reserva",
     *     description="Crear una nueva reserva con los datos proporcionados",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="user", type="integer", description="ID del usuario", example=1),
     *                 @OA\Property(property="space", type="integer", description="ID del espacio", example=1),
     *                 @OA\Property(property="name_event", type="string", description="Nombre del evento", example="Concierto de verano"),
     *                 @OA\Property(property="date_start", type="string", format="date-time", description="Fecha de inicio del evento", example="2025-07-11T05:00:00.000Z"),
     *                 @OA\Property(property="start_time", type="string", description="Hora de inicio", example="00:00"),
     *                 @OA\Property(property="date_end", type="string", format="date-time", description="Fecha de finalización del evento", example="2025-07-11T05:00:00.000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Reserva creada exitosamente"
     *     ),
     *     @OA\Response(
     *         response="400",
     *         description="Solicitud inválida"
     *     )
     * )
     */
    public function createReservation(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {

        $data = json_decode($request->getContent(), true);

        $userId = (int) $data['user'];
        $spaceId = (int) $data['space'];

        $user = $entityManager->getRepository(User::class)->find($userId);
        $space = $entityManager->getRepository(Space::class)->find($spaceId);

        $dateNew = new \DateTime($data['date_start']);
        $formattedNewDate = $dateNew->format('Y-m-d');

        $combinedDateTime = $formattedNewDate . ' ' . $data['start_time'];  // Combina las dos cadenas: '2025-07-11 14:30'

        $reservation = new Reservation();

        $reservation->setUser($user);
        $reservation->setSpace($space);
        $reservation->setNameEvent($data['name_event']);
        $reservation->setDateStart(new \DateTime($combinedDateTime));
        $reservation->setDateEnd(new \DateTime($combinedDateTime));

        $entityManager->persist($reservation);
        $entityManager->flush();


        //Obtenemos los horarios del espacio
        $schedules = $space->getSchedules();
        if (!empty($schedules)) {
            // lo decodificamos para poderlo modificar
            $schedules = json_decode($schedules);

            foreach ($schedules as $key => $value) {
                $date = new \DateTime($value->date);
                $formattedDate = $date->format('Y-m-d');

                //Validamos que si es igual al enviado, entonces que me cambie el available a true ya que es el que me dice si esta disponible
                if ($formattedDate == $formattedNewDate && $value->startTime == $data['start_time']) {
                    $value->available = $reservation->getId();
                }
            }
        }

        //Asigmos el nuevo horaio
        $space->setSchedules(json_encode($schedules));
        //actualizamos el space
        $entityManager->persist($space);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Reserva creada con éxito', 'id' => $reservation->getId()], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/user/reservations', name: 'get_user_reservations', methods: ['POST'])]
    public function getUserReservations(
        Request $request,
        EntityManagerInterface $entityManager,
        ReservationRepository $reservationRepository
    ): JsonResponse {
        $data = json_decode($request->getContent());

        $reservations = $entityManager->getRepository(Reservation::class)->findAll(['user' => $data->userId]);
        // dd($reservations);

        // Llamamos al repositorio para obtener las reservas
        $reservations = $reservationRepository->findByUserId($data->userId);
        // dd($reservations);
        $response = [];
        foreach ($reservations as $item) {

            $spaceEntity = $item->getSpace();
            $spaceId = $spaceEntity->getId();
            $space = $entityManager->getRepository(Space::class)->find($spaceId);

            $spaceInfo = [
                'id' => $space->getId(),
                'name' => $space->getName(),
                'description' => $space->getDescription(),
                'capacity' => $space->getCapacity(),
                'photos' => $space->getPhotos(),
                'schedules' => $space->getSchedules(),
            ];

            $sechedulesJson = json_decode($space->getSchedules() ?? "");
            $dateStart = $item->getDateStart();

            $dateStarFormat = $dateStart->format('Y-m-d');
            $timeStarFormat = $dateStart->format('H:i');

            $schedulesSpace = [];
            foreach ($sechedulesJson as $schedule) {
                $date = new \DateTime($schedule->date);
                $formattedDate = $date->format('Y-m-d');
                if ($formattedDate == $dateStarFormat && $schedule->startTime == $timeStarFormat && $schedule->available == $item->getId()) {
                    $schedulesSpace[] = $schedule;
                }
            }

            $response[] = [
                'id' => $item->getId(),
                'name_event' => $item->getNameEvent(),
                'date_start' => $item->getDateStart(),
                'user' => $data->userId,
                'space_id' => $spaceId,
                'space' => $spaceInfo,
                'schedules' => $schedulesSpace
            ];
        }

        return new JsonResponse($response);
    }

    public function deleteReservation(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);

        if (!$reservation) {
            return new JsonResponse(['message' => 'Reserva no encontrada'], JsonResponse::HTTP_NOT_FOUND);
        }

        $spaceEntity = $reservation->getSpace();
        $spaceId = $spaceEntity->getId();
        $space = $entityManager->getRepository(Space::class)->find($spaceId);
        
        $schedules = $space->getSchedules();
        
        if (!empty($schedules)) {

            $schedules = json_decode($schedules);
            foreach ($schedules as $key => $value) {
                if ((string)$value->available == (string)$id) $value->available = false;
            }
        }

        //Asigmos el nuevo horaio
        $space->setSchedules(json_encode($schedules));
        $entityManager->persist($space);

        // Eliminamos la reserva
        $entityManager->remove($reservation);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Reserva eliminada con éxito'], JsonResponse::HTTP_OK);
    }
}
